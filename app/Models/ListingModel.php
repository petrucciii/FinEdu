<?php

namespace App\Models;

use CodeIgniter\Model;

//i Model tabella `listings` (quotazioni): PK (ticker, mic)
class ListingModel extends Model
{
    protected $table      = 'listings';
    protected $primaryKey = 'ticker';
    protected $returnType = 'array';

    public function findActiveByIsin(string $isin): array
    {
        return $this->db->table($this->table)
            ->select('listings.ticker, listings.mic, listings.isin, listings.active, exchanges.full_name, exchanges.short_name, exchanges.currency_code')
            ->join('exchanges', 'exchanges.mic = listings.mic')
            ->join('currencies', 'currencies.currency_code = exchanges.currency_code')
            ->where('listings.isin', trim($isin))
            ->where('listings.active', 1)
            ->orderBy('listings.mic', 'ASC')
            ->orderBy('listings.ticker', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function insertRow(array $row): bool
    {
        return $this->db->table($this->table)->insert($row);
    }

    public function deleteRow(string $ticker, string $mic): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('ticker', $ticker)
            ->where('mic', $mic)
            ->delete();
    }
}
