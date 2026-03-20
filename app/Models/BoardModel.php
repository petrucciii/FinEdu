<?php

namespace App\Models;

use CodeIgniter\Model;

class BoardModel extends Model
{
    protected $table = 'companies_board'; //
    
    // ci4 doesnt support array as pks so just isin
    protected $primaryKey = 'isin'; 

    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'isin', 
        'member_id', 
        'role', 
        'created_at', 
        'last_update', 
        'id_user'
    ];

    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    public function findBoardPerCompany(string $isin): array
    {
        return $this->select('companies_board.*, board_members.full_name, board_members.picture_path')
                    ->join('board_members', 'board_members.member_id = companies_board.member_id')
                    ->where('isin', trim($isin))
                    ->findAll();
    }
}