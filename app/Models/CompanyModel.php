<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CompanyModel extends Model
{
     //ci standard
     protected $table = 'companies';
     protected $primaryKey = 'isin';
     protected $allowedFields = [
          'name',
          'website',
          'logo_path',
          'country_code',
          'ea_code',
          'active',
          'main_exchange'
     ];

     //automatic update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';



     //read
     public function getCompanyByISIN($isin)
     {
          return $this->select('companies.*, countries.country, sectors.description as sector, exchanges.short_name as main_exchange')
                    ->join('countries', 'countries.country_code = companies.country_code')
                    ->join('sectors', 'sectors.ea_code = companies.ea_code')
                    ->join('exchanges', 'exchanges.mic = companies.main_exchange')
                    ->where('companies.isin', $isin)
                    ->findAll();
     }

}


          