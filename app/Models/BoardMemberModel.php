<?php

namespace App\Models;

use CodeIgniter\Model;

class BoardMemberModel extends Model
{
    protected $table = 'board_members';
    protected $primaryKey = 'member_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'full_name',
        'picture_path',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function findActiveOrdered(): array
    {
        //anagrafica membri CdA attivi, ordinata per compilare select e tabelle admin
        return $this->where('active', 1)
            ->orderBy('full_name', 'ASC')
            ->findAll();
    }
}
