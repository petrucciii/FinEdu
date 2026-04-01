<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CompanyModel extends Model
{
    //ci standard
    protected $table = 'companies';
    protected $primaryKey = 'isin';
    protected $useAutoIncrement = false; //isin è stringa, non auto-increment
    protected $allowedFields = [
        'isin',         //pk stringa: va inclusa per insert
        'name',
        'website',
        'logo_path',
        'country_code',
        'ea_code',
        'active',
        'main_exchange',
        'id_user', // tracciamento admin
    ];

    //automatic update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    //read
    public function getCompanyByISIN($isin)
    {
        return $this->select('companies.*, countries.country, sectors.description as sector, exchanges.short_name as main_exchange_label, currencies.symbol as currency')
            ->join('sectors', 'sectors.ea_code = companies.ea_code', 'left')
            ->join('exchanges', 'exchanges.mic = companies.main_exchange', 'left')
            ->join('countries', 'countries.country_code = companies.country_code', 'left')
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code', 'left')
            ->where('companies.isin', $isin)
            ->first();
    }

    //search and paginate logic
    public function searchAndPaginate(string $searchQuery, int $page)
    {
        //join necessary tables and count active listings per company
        $builder = $this->select('companies.*, sectors.description, countries.country, COUNT(listings.mic) as num_listings')
            ->join('sectors', "sectors.ea_code = companies.ea_code", 'left')
            ->join('countries', "countries.country_code = companies.country_code", 'left')
            ->join('listings', "listings.isin = companies.isin AND listings.active = 1", 'left')
            ->where('companies.active', 1)
            ->groupBy('companies.isin');

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
            'pager' => $this->pager
        ];
    }

    //admin methods

    public function getListings($isin)
    {
        return $this->db->table('listings')
            ->select('listings.*, exchanges.full_name, exchanges.short_name, currencies.currency_code')
            ->join('exchanges', 'exchanges.mic = listings.mic')
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
            ->where('listings.isin', $isin)
            ->where('listings.active', 1)
            ->get()->getResultArray();
    }

    public function getFinancialData($isin)
    {
        return $this->db->table('data')
            ->join('data_type', 'data_type.type_id = data.type_id', 'left')
            ->where('data.isin', $isin)
            ->orderBy('data.year', 'DESC')
            ->get()->getResultArray();
    }

    public function getBoardMembers($isin)
    {
        return $this->db->table('companies_board')
            ->select('board_members.*, companies_board.role')
            ->join('board_members', 'board_members.member_id = companies_board.member_id')
            ->where('companies_board.isin', $isin)
            ->where('board_members.active', 1)
            ->get()->getResultArray();
    }

    //eliminazione logica
    public function deleteCompany($isin, $data)
    {
        //transazione per assicurare che tutte le tabelle vengano aggiornate o nessuna
        $this->db->transStart();

        try {
            $this->update($isin, $data);

            $whereCondition = ['isin' => $isin];

            $this->db->table('listings')->update($data, $whereCondition);
            $this->db->table('companies_board')->update($data, $whereCondition);
            $this->db->table('shareholders')->update($data, $whereCondition);
            $this->db->table('analyst_consensus')->update($data, $whereCondition);

            $this->db->transComplete();

            return $this->db->transStatus();

        } catch (\Throwable $e) {

            return false;
        }
    }
}