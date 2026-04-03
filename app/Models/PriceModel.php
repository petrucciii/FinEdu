<?php

namespace App\Models;

use CodeIgniter\Model;

class PriceModel extends Model
{
    protected $table = 'prices';
    protected $primaryKey = 'price_id';
    protected $allowedFields = ['date', 'ticker', 'mic', 'price'];

    public function insertPrice(string $ticker, string $mic, float $price): bool
    {
        return (bool) $this->insert([
            'ticker' => $ticker,
            'mic' => $mic,
            'price' => round($price, 2),
            'date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getLatestForListing(string $ticker, string $mic): ?array
    {
        return $this->where('ticker', $ticker)
            ->where('mic', $mic)
            ->orderBy('date', 'DESC')
            ->first();
    }

    //ultimi punti per il grafico (più recenti, ordinati cronologicamente)
    public function getSeriesForChart(string $ticker, string $mic, int $maxPoints = 120): array
    {
        $rows = $this->select('date, price')
            ->where('ticker', $ticker)
            ->where('mic', $mic)
            ->orderBy('date', 'DESC')
            ->limit($maxPoints)
            ->findAll();

        return array_reverse($rows);
    }

    /**
     * Serie giornaliera per il grafico: raggruppa per giorno, restituisce il prezzo massimo per ogni giornata.
     * @param string $range  3M|6M|1Y|MAX
     */
    public function getDailySeriesForChart(string $ticker, string $mic, string $range = '1Y'): array
    {
        $builder = $this->builder()
            ->where('ticker', $ticker)
            ->where('mic', $mic);

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
                break;
            default:
                $builder->where('date >=', date('Y-m-d H:i:s', strtotime('-1 year')));
        }

        $builder->select('DATE(date) as d, MAX(price) as price')->groupBy('DATE(date)');

        return $builder->orderBy('d', 'ASC')->get()->getResultArray();
    }

    /**
     * Mappa "ticker|mic" => prezzo ultimo
     * @return array<string, float>
     */
    public function getLatestPriceMap(): array
    {
        $sql = 'SELECT p.ticker, p.mic, p.price FROM prices p
            INNER JOIN (
                SELECT ticker, mic, MAX(date) AS md FROM prices GROUP BY ticker, mic
            ) x ON p.ticker = x.ticker AND p.mic = x.mic AND p.date = x.md'; //trova il prezzo più recente per ogni ticker e mic

        $rows = $this->db->query($sql)->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['ticker'] . '|' . $r['mic']] = (float) $r['price'];
        }

        return $map;
    }
}
