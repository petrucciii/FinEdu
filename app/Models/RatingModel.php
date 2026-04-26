<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    protected $table = 'ratings';
    protected $primaryKey = 'rating_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'rating',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function findActiveOrdered(): array
    {
        //dizionario rating attivi (BUY/HOLD/SELL ecc.) usato dai consensus analisti
        return $this->where('active', 1)
            ->orderBy('rating', 'ASC')
            ->findAll();
    }
}
