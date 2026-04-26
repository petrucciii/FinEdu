<?php

namespace App\Models;

use CodeIgniter\Model;

class CompletedLessonModel extends Model
{
    //tabella reale che salva tentativi e completamenti degli utenti
    protected $table = 'completed_lessons';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'id_lesson',
        'attempt',
        'completed',
        'date',
    ];

    public function getCompletedLessonIds(int $userId): array
    {
        //ritorna gli id delle lezioni completate almeno una volta
        $rows = $this->db->table('completed_lessons')
            ->select('id_lesson')
            ->where('user_id', $userId)
            ->where('completed', 1)
            ->groupBy('id_lesson')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'id_lesson'));
    }

    public function hasCompleted(int $userId, int $lessonId): bool
    {
        //controlla se esiste gia un tentativo completato per la lezione
        return $this->db->table('completed_lessons')
            ->where('user_id', $userId)
            ->where('id_lesson', $lessonId)
            ->where('completed', 1)
            ->countAllResults() > 0;
    }

    public function countAttempts(int $userId, int $lessonId): int
    {
        //conta tutti i tentativi, sia corretti sia sbagliati
        return (int) $this->db->table('completed_lessons')
            ->where('user_id', $userId)
            ->where('id_lesson', $lessonId)
            ->countAllResults();
    }

    public function recordAttempt(int $userId, int $lessonId, bool $completed): bool
    {
        //calcola il prossimo numero tentativo per la chiave primaria composta
        $maxAttempt = $this->db->table('completed_lessons')
            ->selectMax('attempt')
            ->where('user_id', $userId)
            ->where('id_lesson', $lessonId)
            ->get()
            ->getRowArray();

        $attempt = ((int) ($maxAttempt['attempt'] ?? 0)) + 1;

        //salva il tentativo con data corrente e flag completed
        return (bool) $this->db->table('completed_lessons')->insert([
            'user_id' => $userId,
            'id_lesson' => $lessonId,
            'attempt' => $attempt,
            'completed' => $completed ? 1 : 0,
            'date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function countCompletedForUser(int $userId): int
    {
        //conta lezioni diverse completate dall'utente
        return (int) $this->db->table('completed_lessons')
            ->select('id_lesson')
            ->where('user_id', $userId)
            ->where('completed', 1)
            ->groupBy('id_lesson')
            ->countAllResults();
    }

    public function recentAttemptsForUser(int $userId, int $limit = 5): array
    {
        //recupera gli ultimi tentativi con titolo lezione e nome modulo
        return $this->db->table('completed_lessons cl')
            ->select('cl.*, l.title, m.name AS module_name')
            ->join('lessons l', 'l.id_lesson = cl.id_lesson', 'left')
            ->join('modules m', 'm.id_module = l.id_module', 'left')
            ->where('cl.user_id', $userId)
            ->orderBy('cl.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function progressByUsers(string $searchQuery = '', int $page = 1): array
    {
        //costruisce la vista admin dei progressi senza permettere modifiche manuali
        $builder = $this->db->table('users u')
            ->select('u.user_id, u.first_name, u.last_name, u.email, u.experience, levels.level')
            ->select('COUNT(cl.attempt) AS total_attempts', false)
            ->select('COUNT(DISTINCT CASE WHEN cl.completed = 1 THEN cl.id_lesson END) AS completed_lessons', false)
            ->select('SUM(CASE WHEN cl.completed = 0 THEN 1 ELSE 0 END) AS failed_attempts', false)
            ->select('MAX(cl.date) AS last_activity', false)
            ->join('levels', 'levels.level_id = u.level_id', 'left')
            ->join('completed_lessons cl', 'cl.user_id = u.user_id', 'left')
            ->where('u.active', 1)
            ->groupBy('u.user_id');

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $builder->groupStart()
                ->like('u.first_name', $searchQuery)
                ->orLike('u.last_name', $searchQuery)
                ->orLike('u.email', $searchQuery)
                ->groupEnd();
        }

        //conteggio manuale per mantenere compatibile la paginazione con group by
        $builder->orderBy('u.last_name', 'ASC')
            ->orderBy('u.first_name', 'ASC');

        $countSql = 'SELECT COUNT(*) AS c FROM (' . $builder->getCompiledSelect(false) . ') _progress_count';
        $total = (int) ($this->db->query($countSql)->getRow('c') ?? 0);

        $perPage = 10;
        $page = max(1, $page);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'users' => $rows,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pageCount' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }
}
