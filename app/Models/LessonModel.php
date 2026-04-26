<?php

namespace App\Models;

use CodeIgniter\Model;

class LessonModel extends Model
{
    //tabella reale delle lezioni comuni a spiegazioni e quiz
    protected $table = 'lessons';
    protected $primaryKey = 'id_lesson';

    protected $allowedFields = [
        'title',
        'hint',
        'description',
        'id_module',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function findActiveForModule(int $moduleId): array
    {
        //ritorna tutte le lezioni attive di un modulo con dettagli di tipo
        return $this->baseDetailQuery()
            ->where('l.id_module', $moduleId)
            ->where('l.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('l.id_lesson')
            ->orderBy('l.id_lesson', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findDetail(int $lessonId): ?array
    {
        //recupera una singola lezione con modulo e dati della specializzazione
        $row = $this->baseDetailQuery()
            ->where('l.id_lesson', $lessonId)
            ->where('l.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('l.id_lesson')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function findQuizLessonsForAdmin(): array
    {
        //lista solo le lezioni che hanno almeno una riga nella tabella questions
        return $this->baseDetailQuery()
            ->where('l.active', 1)
            ->where('m.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('l.id_lesson')
            ->having('COUNT(DISTINCT q.id_question) > 0', null, false)
            ->orderBy('m.id_module', 'ASC')
            ->orderBy('l.id_lesson', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function baseDetailQuery()
    {
        /*query comune per riconoscere la specializzazione della lesson.
        se esistono righe in questions la lezione e un quiz,
        se esiste una riga in explanations la lezione e una spiegazione.*/
        return $this->db->table('lessons l')
            ->select('l.*')
            ->select('m.name AS module_name, m.description AS module_description')
            ->select('MAX(e.id_explanation) AS id_explanation', false)
            ->select('MAX(e.body) AS body', false)
            ->select('CASE WHEN COUNT(DISTINCT q.id_question) > 0 THEN 1 ELSE 0 END AS question_count', false)
            ->select('COALESCE(MAX(q.experience), 0) AS experience', false)
            ->select("CASE
                WHEN COUNT(DISTINCT q.id_question) > 0 THEN 'quiz'
                WHEN COUNT(DISTINCT e.id_explanation) > 0 THEN 'explanation'
                ELSE 'lesson'
            END AS lesson_type", false)
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('modules m', 'm.id_module = l.id_module', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('explanations e', 'e.id_lesson = l.id_lesson AND e.active = 1', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('questions q', 'q.id_lesson = l.id_lesson AND q.active = 1', 'left');
    }
}
