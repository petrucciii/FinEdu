<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    //tabella reale del set risposte associato a una lesson quiz
    protected $table = 'questions';
    protected $primaryKey = 'id_question';

    protected $allowedFields = [
        'id_lesson',
        'experience',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function findActive(int $questionId): ?array
    {
        //recupera un set risposte attivo.
        $row = $this->where('id_question', $questionId)
            ->where('active', 1)
            ->first();

        return $row ?: null;
    }

    public function findByLesson(int $lessonId): array
    {
        //tutte le domande collegate alla lesson, anche se gia disattivate.
        return $this->where('id_lesson', $lessonId)->findAll();
    }

    public function countActiveByLesson(int $lessonId): int
    {
        //un quiz deve avere un solo set attivo di quattro risposte.
        return (int) $this->where('id_lesson', $lessonId)
            ->where('active', 1)
            ->countAllResults();
    }

    public function findWithAnswersByLesson(int $lessonId): array
    {
        //carica le righe questions e ci aggancia le risposte attive
        $questions = $this->where('id_lesson', $lessonId)
            ->where('active', 1)
            ->orderBy('id_question', 'ASC')
            ->findAll();

        $answerModel = model(AnswerModel::class);
        foreach ($questions as &$question) {
            $question['answers'] = $answerModel->findActiveByQuestion((int) $question['id_question']);
        }
        unset($question);

        return $questions;
    }
}
