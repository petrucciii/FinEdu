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
        //carica i moduli con conteggi utili alla tabella admin
        return $this->db->table('modules m')
            ->select('m.*')
            ->select('COUNT(DISTINCT l.id_lesson) AS lesson_count', false)
            ->select('COUNT(DISTINCT e.id_explanation) AS explanation_count', false)
            ->select('COUNT(DISTINCT CASE WHEN q.id_question IS NOT NULL THEN l.id_lesson END) AS quiz_count', false)
            ->join('lessons l', 'l.id_module = m.id_module AND l.active = 1', 'left')
            ->join('explanations e', 'e.id_lesson = l.id_lesson AND e.active = 1', 'left')
            ->join('questions q', 'q.id_lesson = l.id_lesson AND q.active = 1', 'left')
            ->where('m.active', 1)
            ->groupBy('m.id_module')
            ->orderBy('m.id_module', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findProgressForUser(int $userId): array
    {
        /*calcola il progresso dei moduli per un utente.
        usa completed_lessons per sapere quali lezioni sono completate,
        senza inventare campi non presenti nello schema.*/
        $completedSubquery = '(SELECT 1 FROM completed_lessons cl
            WHERE cl.user_id = ' . (int) $userId . '
            AND cl.id_lesson = l.id_lesson
            AND cl.completed = 1)';

        return $this->db->table('modules m')
            ->select('m.*')
            ->select('COUNT(DISTINCT l.id_lesson) AS lesson_count', false)
            ->select('COUNT(DISTINCT CASE WHEN EXISTS ' . $completedSubquery . ' THEN l.id_lesson END) AS completed_count', false)
            ->select('COALESCE(SUM(q.experience), 0) AS total_experience', false)
            ->join('lessons l', 'l.id_module = m.id_module AND l.active = 1', 'left')
            ->join('questions q', 'q.id_lesson = l.id_lesson AND q.active = 1', 'left')
            ->where('m.active', 1)
            ->groupBy('m.id_module')
            ->orderBy('m.id_module', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function countActiveLessons(): int
    {
        //serve alla pagina admin progressi per calcolare la percentuale globale
        return (int) $this->db->table('lessons')
            ->where('active', 1)
            ->countAllResults();
    }
}
