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
        return $this->select('companies.*, countries.country, sectors.description as sector, exchanges.short_name as main_exchange, currencies.symbol as currency')
                    ->join('sectors', 'sectors.ea_code = companies.ea_code')
                    ->join('exchanges', 'exchanges.mic = companies.main_exchange')
                    ->join('countries', 'countries.country_code = exchanges.country_code')
                    ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
                    ->where('companies.isin', $isin)
                    ->findAll();
    }

    //search and paginate logic moved from controller
    public function searchAndPaginate(string $searchQuery, int $page)
    {
        //we join necessary tables to perform the search
        $builder = $this->join('sectors', "sectors.ea_code = companies.ea_code")
                        ->join('countries', "countries.country_code = companies.country_code")
                        ->join('listings', "listings.isin = companies.isin");

        $searchQuery = trim($searchQuery);
        if ($searchQuery != '') {
            $builder->groupStart()
                    ->like('companies.name', $searchQuery)
                    ->orLike('companies.isin', $searchQuery)
                    ->orLike('listings.ticker', $searchQuery)
                    ->groupEnd();
        }

        //return paginated results and the pager object
        return [
            'companies' => $builder->paginate(10, 'default', $page),
            'pager'     => $this->pager
        ];
    }
}