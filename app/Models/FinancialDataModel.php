<?php

namespace App\Models;

use CodeIgniter\Model;

//i Model tabella `data` (bilanci): PK (isin, year) gestita con Query Builder
class FinancialDataModel extends Model
{
    protected $table = 'data';
    protected $primaryKey = 'isin';
    protected $returnType = 'array';

    /** Campi numerici opzionali (BIGINT NULL) */
    public const BIGINT_FIELDS = [
        'revenues',
        'amortizations_depretiations',
        'income_taxes',
        'interests',
        'net_profit',
        'net_debt',
        'share_number',
        'free_cash_flow',
        'capex',
        'dividends',
    ];

    public function findDataPerCompany(string $isin): array
    {
        return $this->db->table($this->table)
            ->select('data.*, data_type.type, data_type.name AS type_name')
            ->join('data_type', 'data_type.type_id = data.type_id', 'left')
            ->where('data.isin', trim($isin))
            ->orderBy('data.year', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function insertRow(array $row): bool
    {
        return $this->db->table($this->table)->insert($row);
    }

    public function updateRow(string $isin, int $year, array $row): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('year', $year)
            ->update($row);
    }

    public function deleteRow(string $isin, int $year): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('year', $year)
            ->delete();
    }

    //verifica se esiste già un bilancio per ISIN + anno (import XML / upsert)
    public function hasYear(string $isin, int $year): bool
    {
        return $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('year', $year)
            ->countAllResults() > 0;
    }
}
