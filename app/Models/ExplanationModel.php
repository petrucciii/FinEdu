<?php

namespace App\Models;

use CodeIgniter\Model;

class ExplanationModel extends Model
{
    //tabella reale della specializzazione spiegazione di una lesson
    protected $table = 'explanations';
    protected $primaryKey = 'id_explanation';

    protected $allowedFields = [
        'id_lesson',
        'body',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function deactivateByLesson(int $lessonId, int $userId): bool
    {
        //soft delete di tutte le spiegazioni collegate a una lezione.
        return (bool) $this->where('id_lesson', $lessonId)
            ->set([
                'active' => 0,
                'id_user' => $userId,
            ])
            ->update();
    }
}
