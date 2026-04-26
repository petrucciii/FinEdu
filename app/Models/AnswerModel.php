<?php

namespace App\Models;

use CodeIgniter\Model;

class AnswerModel extends Model
{
    //tabella reale delle risposte possibili per un quiz
    protected $table = 'answers';
    protected $primaryKey = 'id_answer';

    protected $allowedFields = [
        'answer',
        'id_question',
        'is_correct',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function findActiveByQuestion(int $questionId): array
    {
        //risposte attive ordinate per un set quiz.
        return $this->where('id_question', $questionId)
            ->where('active', 1)
            ->orderBy('id_answer', 'ASC')
            ->findAll();
    }

    public function deactivateByQuestion(int $questionId, int $userId): bool
    {
        //soft delete delle risposte di un set quiz.
        return (bool) $this->where('id_question', $questionId)
            ->set([
                'active' => 0,
                'id_user' => $userId,
            ])
            ->update();
    }

    public function insertManyForQuestion(int $questionId, array $answers, int $userId): void
    {
        //inserisce le quattro risposte normalizzate dal controller.
        foreach ($answers as $answer) {
            $this->insert([
                'id_question' => $questionId,
                'answer' => $answer['answer'],
                'is_correct' => (int) $answer['is_correct'],
                'id_user' => $userId,
                'active' => 1,
            ]);
        }
    }
}
