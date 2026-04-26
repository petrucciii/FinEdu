<?php

namespace App\Models;

use CodeIgniter\Model;

class EducationModuleModel extends Model
{
    //tabella reale dei macro argomenti del percorso educativo
    protected $table = 'modules';
    protected $primaryKey = 'id_module';

    protected $allowedFields = [
        'description',
        'name',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function findActiveOrdered(): array
    {
        //ritorna i moduli attivi ordinati per id_module
        return $this->where('active', 1)
            ->orderBy('id_module', 'ASC')
            ->findAll();
    }

    public function findActiveById(int $moduleId): ?array
    {
        //recupera un modulo attivo per id, centralizzando la condizione active.
        $row = $this->where('id_module', $moduleId)
            ->where('active', 1)
            ->first();

        return $row ?: null;
    }

    public function countActive(): int
    {
        //conta i moduli attivi per dashboard e viste admin.
        return (int) $this->where('active', 1)->countAllResults();
    }

    public function findForAdmin(): array
    {
        /*
         * Carica i moduli con i conteggi mostrati nella pagina admin.
         *
         * Le LEFT JOIN sono volute: un modulo deve comparire anche se non ha ancora
         * lezioni, spiegazioni o quiz. I COUNT DISTINCT evitano conteggi duplicati quando
         * una lezione ha piu' spiegazioni o piu' domande quiz collegate.
         */
        return $this->db->table('modules m')
            ->select('m.*')
            ->select('COUNT(DISTINCT l.id_lesson) AS lesson_count', false)
            ->select('COUNT(DISTINCT e.id_explanation) AS explanation_count', false)
            ->select('COUNT(DISTINCT CASE WHEN q.id_question IS NOT NULL THEN l.id_lesson END) AS quiz_count', false)
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('lessons l', 'l.id_module = m.id_module AND l.active = 1', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('explanations e', 'e.id_lesson = l.id_lesson AND e.active = 1', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('questions q', 'q.id_lesson = l.id_lesson AND q.active = 1', 'left')
            ->where('m.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('m.id_module')
            ->orderBy('m.id_module', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findProgressForUser(int $userId): array
    {
        /*
         * Calcola il progresso dei moduli per un utente.
         *
         * completed_lessons salva tentativi e completamenti; non esiste una colonna
         * "progress" sui moduli. Per questo usiamo una subquery EXISTS che controlla se
         * per quella lezione esiste almeno un tentativo completato dall'utente.
         * EXISTS e COUNT DISTINCT evitano di contare piu' volte la stessa lezione se
         * l'utente l'ha superata dopo vari tentativi.
         */
        $completedSubquery = '(SELECT 1 FROM completed_lessons cl
            WHERE cl.user_id = ' . (int) $userId . '
            AND cl.id_lesson = l.id_lesson
            AND cl.completed = 1)';

        return $this->db->table('modules m')
            ->select('m.*')
            ->select('COUNT(DISTINCT l.id_lesson) AS lesson_count', false)
            //exists controlla la presenza di righe collegate senza duplicare i risultati della query principale
            ->select('COUNT(DISTINCT CASE WHEN EXISTS ' . $completedSubquery . ' THEN l.id_lesson END) AS completed_count', false)
            ->select('COALESCE(SUM(q.experience), 0) AS total_experience', false)
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('lessons l', 'l.id_module = m.id_module AND l.active = 1', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('questions q', 'q.id_lesson = l.id_lesson AND q.active = 1', 'left')
            ->where('m.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('m.id_module')
            ->orderBy('m.id_module', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function progressSummaryForUser(int $userId): array
    {
        /*
         * Prepara un riepilogo gia pronto per le view profilo/admin.
         * Centralizzare qui il calcolo evita di duplicare percentuali e stati nei controller.
         */
        $modules = $this->withProgressStatuses($this->findProgressForUser($userId));

        $totalLessons = 0;
        $completedLessons = 0;
        foreach ($modules as $module) {
            $totalLessons += (int) ($module['lesson_count'] ?? 0);
            $completedLessons += (int) ($module['completed_count'] ?? 0);
        }

        return [
            'modules' => $modules,
            'totalLessons' => $totalLessons,
            'completedLessons' => $completedLessons,
            'progressPercent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
        ];
    }

    public function countActiveLessons(): int
    {
        //serve alla pagina admin progressi per calcolare la percentuale globale
        return (int) $this->db->table('lessons')
            ->where('active', 1)
            ->countAllResults();
    }

    private function withProgressStatuses(array $modules): array
    {
        /*
         * Calcola lo stato progressivo dei moduli.
         *
         * La regola del percorso e' sequenziale: un modulo successivo risulta "locked"
         * finche' quello precedente non e' completato. Per questo manteniamo
         * $previousCompleted mentre scorriamo i moduli ordinati per id_module.
         */
        $previousCompleted = true;

        foreach ($modules as &$module) {
            $lessonCount = (int) ($module['lesson_count'] ?? 0);
            $completedCount = (int) ($module['completed_count'] ?? 0);
            $module['progress_percent'] = $lessonCount > 0 ? (int) round(($completedCount / $lessonCount) * 100) : 0;

            if (!$previousCompleted) {
                $module['status'] = 'locked';
            } elseif ($lessonCount > 0 && $completedCount >= $lessonCount) {
                $module['status'] = 'completed';
            } elseif ($completedCount > 0) {
                $module['status'] = 'in_progress';
            } else {
                $module['status'] = 'available';
            }

            $previousCompleted = $previousCompleted && ($lessonCount === 0 || $completedCount >= $lessonCount);
        }
        unset($module);

        return $modules;
    }
}
