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
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('sectors', 'sectors.ea_code = companies.ea_code', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('exchanges', 'exchanges.mic = companies.main_exchange', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('countries', 'countries.country_code = companies.country_code', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code', 'left')
            ->where('companies.isin', $isin)
            ->where('companies.active', 1)
            ->first();//prende solo la prima (l'unica), cosi da non avere un array dentro un altro array come in findAll()
    }

    public function findActiveOrdered(): array
    {
        //elenco aziende attive per select e filtri admin.
        return $this->where('active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function countActive(): int
    {
        //conta aziende attive per dashboard admin.
        return (int) $this->where('active', 1)->countAllResults();
    }

    public function findLatestActive(): ?array
    {
        //serve alla home per mostrare la nuova societa solo se il dato esiste nel db
        $row = $this->where('active', 1)
            ->orderBy('created_at', 'DESC')
            ->orderBy('isin', 'DESC')
            ->first();

        return $row ?: null;
    }

    /*
     * Ricerca e impagina risultato per la view elenco società.
     *
     * Le LEFT JOIN portano descrizione settore, paese e numero quotazioni senza perdere
     * società che hanno dati incompleti. Il groupBy serve perché una società può avere
     * più listing: senza raggruppare avremmo una riga duplicata per ogni borsa.
     */
    public function searchAndPaginate(string $searchQuery, int $page)
    {
        $builder = $this->select('companies.*, sectors.description, countries.country, COUNT(listings.mic) as num_listings')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('sectors', "sectors.ea_code = companies.ea_code", 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('countries', "countries.country_code = companies.country_code", 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('listings', "listings.isin = companies.isin AND listings.active = 1", 'left')
            ->where('companies.active', 1)
            //raggruppo per evitare duplicati creati dalle join uno-a-molti
            ->groupBy('companies.isin');

        /*
         * Un solo input cerca nome società, ISIN e ticker. La query è divisa in parole
         * così ricerche composte continuano a funzionare come nelle news/admin utenti.
         */
        $tokens = preg_split('/\s+/', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $builder->groupStart()
                ->like('companies.name', $token)
                ->orLike('companies.isin', $token)
                ->orLike('listings.ticker', $token)
                ->groupEnd();
        }

        //ritorna le companie in varie pagine
        return [
            //paginazione lato database per non caricare tutto in memoria e mantenere la risposta veloce
            'companies' => $builder->paginate(10, 'default', $page),//10 per pagina e pagina correte (senno 1)
            'pager' => $this->pager
        ];
    }

    //elenco quotazioni con join necessarrie
    public function getListings($isin)
    {
        return $this->db->table('listings')
            ->select('listings.*, exchanges.full_name, exchanges.short_name, currencies.currency_code')
            //uso la join per arricchire il record principale con dati collegati senza fare query separate
            ->join('exchanges', 'exchanges.mic = listings.mic')
            //uso la join per arricchire il record principale con dati collegati senza fare query separate
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
            ->where('listings.isin', $isin)
            ->where('listings.active', 1)
            ->get()->getResultArray();
    }



    public function hasDependencies(string $isin): bool
    {
        //una societa si puo disattivare solo se non ha dati collegati
        $isin = trim($isin);
        $checks = [
            ['listings', 'isin'],
            ['data', 'isin'],
            ['companies_board', 'isin'],
            ['companies_shareholders', 'isin'],
            ['companies_news', 'isin'],
            ['analysts_consensus', 'isin'],
        ];

        foreach ($checks as [$table, $field]) {
            if ((int) $this->db->table($table)->where($field, $isin)->countAllResults() > 0) {
                return true;
            }
        }

        return false;
    }

    //eliminazione logica: non rimuove record, disattiva solo la societa senza toccare dati collegati
    public function deleteCompany($isin, $data)
    {
        try {
            $payload = [
                'active' => 0,
                'id_user' => $data['id_user'] ?? null,
            ];

            return $this->update($isin, $payload);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
