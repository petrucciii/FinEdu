<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingModel extends Model
{
    protected $table = 'listings';
    protected $primaryKey = 'ticker';

    protected $returnType = 'array';

    protected $allowedFields = ['ticker', 'mic', 'isin', 'active'];

    //trova listing di una società
    public function findActiveByIsin(string $isin): array
    {
        return $this
            ->select('listings.ticker, listings.mic, listings.isin, listings.active, exchanges.full_name, exchanges.short_name, exchanges.currency_code')
            //uso la join per arricchire il record principale con dati collegati senza fare query separate
            ->join('exchanges', 'exchanges.mic = listings.mic')
            //uso la join per arricchire il record principale con dati collegati senza fare query separate
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
            ->where('listings.isin', trim($isin))
            ->where('listings.active', 1)
            ->orderBy('listings.mic', 'ASC')
            ->orderBy('listings.ticker', 'ASC')
            ->findAll();
    }

    public function findActiveByTickerMic(string $ticker, string $mic): ?array
    {
        //verifica che una quotazione sia presente e attiva.
        $row = $this->where('ticker', trim($ticker))
            ->where('mic', trim($mic))
            ->where('active', 1)
            ->first();

        return $row ?: null;
    }

    public function insertRow(array $row): bool
    {
        try {
            return $this->insert($row);
        } catch (\Throwable $th) {
            return false;
        }

    }

    public function deleteRow(string $ticker, string $mic): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('ticker', trim($ticker))
                ->where('mic', trim($mic))
                ->update(['active' => 0]);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //listings attive con MIC per Yahoo, solo borse attualmente aperte (CET) 
    public function findAllActiveForQuotes(): array|bool
    {

        //ora corrente nel fuso orario CET (Europe/Rome)
        $nowCet = (new \DateTime('now', new \DateTimeZone('Europe/Rome')))->format('H:i:s');
        //  \ prima di DateTime serve per indicare che stiamo usando la classe DateTime del namespace globale
        $dayOfWeek = (new \DateTime('now', new \DateTimeZone('Europe/Rome')))->format('l');
        if ($dayOfWeek != 'Saturday' && $dayOfWeek != 'Sunday') {
            return $this->select('listings.ticker, listings.mic')
                //uso la join per arricchire il record principale con dati collegati senza fare query separate
                ->join('exchanges', 'exchanges.mic = listings.mic')
                ->where('listings.active', 1)
                ->where('exchanges.active', 1)
                ->where('exchanges.opening_hour <=', $nowCet)//controllo borsa aperta
                ->where('exchanges.closing_hour >=', $nowCet)
                ->findAll();
        } else {
            return false;
        }
    }

    /*
     * Ricerca paginata dei listing per la pagina quotazioni utente.
     *
     * La subquery last_price prende il prezzo più recente per ogni coppia ticker/mic:
     * evita una seconda chiamata AJAX solo per mostrare il prezzo nella tabella.
     * Il filtro mic limita a una singola borsa, mentre la ricerca tokenizzata lavora su
     * ticker, ISIN e nome società dallo stesso input.
     */
    public function searchPaginate(string $searchQuery, int $page, string $mic = ''): array
    {
        $builder = $this->select('listings.ticker, listings.mic, listings.isin, listings.active,
                exchanges.full_name as exchange_name, exchanges.short_name,
                companies.name as company_name,
                (SELECT p.price FROM prices p WHERE p.ticker = listings.ticker AND p.mic = listings.mic ORDER BY p.date DESC LIMIT 1) as last_price')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('exchanges', 'exchanges.mic = listings.mic', 'left')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('listings.active', 1);

        $tokens = preg_split('/\s+/', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $builder->groupStart()
                ->like('listings.ticker', $token)
                ->orLike('listings.isin', $token)
                ->orLike('companies.name', $token)
                ->groupEnd();
        }

        //filtro per borsa (mic)
        if ($mic !== '') {
            $builder->where('listings.mic', $mic);
        }

        $builder->orderBy('listings.ticker', 'ASC');

        return [
            //paginazione lato database per non caricare tutto in memoria e mantenere la risposta veloce
            'listings' => $builder->paginate(15, 'default', $page),
            'pager' => $this->pager,
        ];
    }
}
