<?php

namespace App\Models;

use CodeIgniter\Model;

class ShareholderModel extends Model
{
    protected $table = 'companies_shareholders'; 
    
    protected $primaryKey = 'isin'; 

    protected $allowedFields = [
        'isin', 
        'firm_id', 
        'ownership', 
        'created_at', 
        'last_update', 
        'id_user'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

   
    public function findShareholdersPerCompany(string $isin): array
    {
        return $this->select('companies_shareholders.*, firms.firm_name')
                    ->join('firms', 'firms.firm_id = companies_shareholders.firm_id')
                    ->where('companies_shareholders.isin', trim($isin))
                    ->orderBy('ownership', 'DESC')
                    ->findAll();
    }
}