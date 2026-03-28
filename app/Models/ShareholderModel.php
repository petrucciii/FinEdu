<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tabella `companies_shareholders`. PK (firm_id, isin).
 */
class ShareholderModel extends Model
{
    protected $table      = 'companies_shareholders';
    protected $primaryKey = 'isin';
    protected $returnType = 'array';

    public function findShareholdersPerCompany(string $isin): array
    {
        return $this->db->table($this->table)
            ->select('companies_shareholders.isin, companies_shareholders.firm_id, companies_shareholders.ownership, firms.firm_name')
            ->join('firms', 'firms.firm_id = companies_shareholders.firm_id')
            ->where('companies_shareholders.isin', trim($isin))
            ->orderBy('companies_shareholders.ownership', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function insertRow(array $row): bool
    {
        return $this->db->table($this->table)->insert($row);
    }

    public function updateRow(string $isin, int $firmId, array $row): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('firm_id', $firmId)
            ->update($row);
    }

    public function deleteRow(string $isin, int $firmId): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('firm_id', $firmId)
            ->delete();
    }
}
