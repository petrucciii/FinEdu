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
        'id_user', 
    ];

    //automatic update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    //compagnia da isin con join per dati necessari alla view (non gli id o i codici ma descrizioni)
    public function getCompanyByISIN($isin)
    {
        return $this->select('companies.*, countries.country, sectors.description as sector, exchanges.short_name as main_exchange_label, currencies.symbol as currency')
            ->join('sectors', 'sectors.ea_code = companies.ea_code', 'left')
            ->join('exchanges', 'exchanges.mic = companies.main_exchange', 'left')
            ->join('countries', 'countries.country_code = companies.country_code', 'left')
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code', 'left')
            ->where('companies.isin', $isin)
            ->where('companies.active', 1)
            ->first();//prende solo la prima (l'unica), cosi da non avere un array dentro un altro array come in findAll()
    }

    //ricerca e impagina risultato per la view di elenco società
    public function searchAndPaginate(string $searchQuery, int $page)
    {
        //join 
        $builder = $this->select('companies.*, sectors.description, countries.country, COUNT(listings.mic) as num_listings')
            ->join('sectors', "sectors.ea_code = companies.ea_code", 'left')
            ->join('countries', "countries.country_code = companies.country_code", 'left')
            ->join('listings', "listings.isin = companies.isin AND listings.active = 1", 'left')
            ->where('companies.active', 1)
            ->groupBy('companies.isin');

        //filtra in base alla query di ricerca ricevuta su nome isin e ticker
        $searchQuery = trim($searchQuery);
        if ($searchQuery != '') {
            $builder->groupStart()//raggruppamento condizioni
                ->like('companies.name', $searchQuery)
                ->orLike('companies.isin', $searchQuery)
                ->orLike('listings.ticker', $searchQuery)
                ->groupEnd();
        }

        //ritorna le companie in varie pagine
        return [
            'companies' => $builder->paginate(10, 'default', $page),//10 per pagina e pagina correte (senno 1)
            'pager' => $this->pager
        ];
    }

    //elenco quotazioni con join necessarrie
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



    //eliminazione logica
    public function deleteCompany($isin, $data)
    {
        //transazione per assicurare che tutte le tabelle vengano aggiornate o nessuna
        $this->db->transStart();

        try {
            $data = array_push($data, 'active', 0); //aggiunge active=0 ai dati ricevuti (es. id_user) per tenere traccia di chi ha fatto l'operazione
            $data = ['active' => 0];
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