<?php

namespace App\Models;

use CodeIgniter\Model;

class FirmModel extends Model
{
    protected $table = 'firms';
    protected $primaryKey = 'firm_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'firm_name',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function findActiveOrdered(): array
    {
        //elenco società di analisi attive usato nei select consensus admin
        return $this->where('active', 1)
            ->orderBy('firm_name', 'ASC')
            ->findAll();
    }
}
