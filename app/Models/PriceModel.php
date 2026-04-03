<?php

namespace App\Models;

use CodeIgniter\Model;

//model per la tabella `prices`, gestisce i prezzi storici delle quotazioni.
//ogni riga rappresenta un prezzo di un listing (ticker+mic) in un dato momento
class PriceModel extends Model
{
    protected $table = 'prices';
    protected $primaryKey = 'price_id';
    protected $allowedFields = ['date', 'ticker', 'mic', 'price'];

    //inserisce un nuovo prezzo per un listing.
    //viene chiamato dal command UpdatePrices (cron job) ogni 15 minuti
    public function insertPrice(string $ticker, string $mic, float $price): bool
    {
        return (bool) $this->insert([
            'ticker' => $ticker,
            'mic' => $mic,
            'price' => round($price, 2),
            'date' => date('Y-m-d H:i:s'),
        ]);
    }

    //recupera l'ultimo prezzo disponibile per un listing specifico.
    //usato nella viewCompany per mostrare il prezzo corrente in alto a destra
    public function getLatestForListing(string $ticker, string $mic): ?array
    {
        return $this->where('ticker', $ticker)
            ->where('mic', $mic)
            ->orderBy('date', 'DESC')
            ->first();
    }

    //serie giornaliera per il grafico: raggruppa per giorno e prende il prezzo massimo
    //di ogni giornata per evitare troppi punti (un cron ogni 15min = ~30 punti/giorno).
    //il filtro temporale dipende dal range: 3M, 6M, 1Y oppure MAX (tutti i dati).
    //usa DATE(date) nel GROUP BY per rispettare sql_mode=only_full_group_by di mysql
    public function getDailySeriesForChart(string $ticker, string $mic, string $range = '1Y'): array
    {
        $builder = $this->builder()
            ->where('ticker', $ticker)
            ->where('mic', $mic);

        //applica il filtro di data in base al range selezionato dall'utente
        switch ($range) {
            case '3M':
                $builder->where('date >=', date('Y-m-d H:i:s', strtotime('-3 months')));
                break;
            case '6M':
                $builder->where('date >=', date('Y-m-d H:i:s', strtotime('-6 months')));
                break;
            case '1Y':
                $builder->where('date >=', date('Y-m-d H:i:s', strtotime('-1 year')));
                break;
            case 'MAX':
                //nessun filtro, prende tutti i dati storici
                break;
            default:
                $builder->where('date >=', date('Y-m-d H:i:s', strtotime('-1 year')));
        }

        //raggruppa per giorno e prende il prezzo massimo di ogni giornata
        $builder->select('DATE(date) as d, MAX(price) as price')->groupBy('DATE(date)');

        return $builder->orderBy('d', 'ASC')->get()->getResultArray();
    }

    //costruisce una mappa chiave=>valore con l'ultimo prezzo per ogni listing.
    //la chiave e "ticker|mic", il valore e il prezzo float.
    //usa una subquery per trovare la data piu recente per ogni coppia ticker/mic,
    //poi fa join sulla tabella principale per recuperare il prezzo corrispondente
    public function getLatestPriceMap(): array
    {
        $sql = 'SELECT p.ticker, p.mic, p.price FROM prices p
            INNER JOIN (
                SELECT ticker, mic, MAX(date) AS md FROM prices GROUP BY ticker, mic
            ) x ON p.ticker = x.ticker AND p.mic = x.mic AND p.date = x.md';

        $rows = $this->db->query($sql)->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['ticker'] . '|' . $r['mic']] = (float) $r['price'];
        }

        return $map;
    }
}
