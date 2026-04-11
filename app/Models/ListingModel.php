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
            ->join('exchanges', 'exchanges.mic = listings.mic')
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
            ->where('listings.isin', trim($isin))
            ->where('listings.active', 1)
            ->orderBy('listings.mic', 'ASC')
            ->orderBy('listings.ticker', 'ASC')
            ->findAll();
    }

    public function insertRow(array $row): bool
    {
        return $this->insert($row);
    }

    public function deleteRow(string $ticker, string $mic): bool
    {
        return $this->where('ticker', $ticker)
            ->where('mic', $mic)
            ->update(['active' => 0]);
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

    //ricerca paginata dei listings con join exchange e ultimo prezzo per la pagina listings utente.
    //supporta ricerca per ticker/isin/nome societa e filtro per mic (borsa).
    //il prezzo viene recuperato con una subquery che prende il piu recente per ogni ticker+mic
    public function searchPaginate(string $searchQuery, int $page, string $mic = ''): array
    {
        $builder = $this->select('listings.ticker, listings.mic, listings.isin, listings.active,
                exchanges.full_name as exchange_name, exchanges.short_name,
                companies.name as company_name,
                (SELECT p.price FROM prices p WHERE p.ticker = listings.ticker AND p.mic = listings.mic ORDER BY p.date DESC LIMIT 1) as last_price')
            ->join('exchanges', 'exchanges.mic = listings.mic', 'left')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('listings.active', 1);

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $builder->groupStart()
                ->like('listings.ticker', $searchQuery)
                ->orLike('listings.isin', $searchQuery)
                ->orLike('companies.name', $searchQuery)
                ->groupEnd();
        }

        //filtro per borsa (mic)
        if ($mic !== '') {
            $builder->where('listings.mic', $mic);
        }

        $builder->orderBy('listings.ticker', 'ASC');

        return [
            'listings' => $builder->paginate(15, 'default', $page),
            'pager' => $this->pager,
        ];
    }
}
