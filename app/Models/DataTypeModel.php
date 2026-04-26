<?php

namespace App\Models;

use CodeIgniter\Model;

class DataTypeModel extends Model
{
    protected $table = 'data_type';
    protected $primaryKey = 'type_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'type',
        'name',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function findActiveOrdered(): array
    {
        //tabella dizionario per i tipi di dato finanziario: solo record attivi per select/import
        return $this->where('active', 1)
            ->orderBy('type_id', 'ASC')
            ->findAll();
    }
}
