<?php

namespace App\Models;

use CodeIgniter\Model;

class NewspaperModel extends Model
{
    protected $table = 'newspapers';
    protected $primaryKey = 'newspaper_id';
    protected $allowedFields = ['newspaper', 'id_user', 'active'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    //read
    public function listActive(): array
    {
        return $this->where('active', 1)->orderBy('newspaper', 'ASC')->findAll();
    }
}
