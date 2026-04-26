<?php

namespace App\Models;

use CodeIgniter\Model;

//i Model tabella `data` (bilanci): PK (isin, year) gestita con Query Builder
class FinancialDataModel extends Model
{
    protected $table = 'data';
    protected $primaryKey = 'isin';
    //serve a findAll() per restiture array associativi e non oggetti
    protected $returnType = 'array';

    //colonne per conversione a label e formattazione dati in controller
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


    //bilanci di una specifica società con join su tabelle dizionario
    public function findDataPerCompany(string $isin): array
    {
        return $this->select('data.*, data_type.type, data_type.name AS type_name')
            //left join: tengo comunque il record principale anche se il dato collegato manca
            ->join('data_type', 'data_type.type_id = data.type_id', 'left')
            ->where('data.isin', trim($isin))
            ->orderBy('data.year', 'DESC')
            ->findAll();
    }


    //insert
    public function insertRow(array $row): bool
    {
        try {
            return $this->db->table($this->table)->insert($row);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //update
    public function updateRow(string $isin, int $year, array $row): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('year', $year)
                ->update($row);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //delete
    public function deleteRow(string $isin, int $year): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('year', $year)
                ->delete();
        } catch (\Throwable $th) {
            return false;
        }
    }

    //verifica se esiste già un bilancio per ISIN+anno (import XML)
    public function hasYear(string $isin, int $year): bool
    {
        try {
            return $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('year', $year)
                ->countAllResults() > 0;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
