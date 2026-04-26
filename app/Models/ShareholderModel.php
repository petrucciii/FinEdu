<?php

namespace App\Models;

use CodeIgniter\Model;

class ShareholderModel extends Model
{
    protected $table = 'companies_shareholders';
    protected $primaryKey = 'isin';
    protected $returnType = 'array';

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    //trova azionisti di una societa ordinati per quota di possesso
    public function findShareholdersPerCompany(string $isin)
    {
        try {
            return $this->select('companies_shareholders.isin, companies_shareholders.firm_id, companies_shareholders.ownership, firms.firm_name')
                //uso la join per arricchire il record principale con dati collegati senza fare query separate
                ->join('firms', 'firms.firm_id = companies_shareholders.firm_id')
                ->where('companies_shareholders.isin', trim($isin))
                ->orderBy('companies_shareholders.ownership', 'DESC')
                ->findAll();
        } catch (\Throwable $th) {
            return false;
        }
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

    public function updateRow(string $isin, int $firmId, array $row): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('firm_id', $firmId)
                ->update($row);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //cancellazione riga (non soft, non serve tenere storico azionisti)
    public function deleteRow(string $isin, int $firmId): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('firm_id', $firmId)
                ->delete();
        } catch (\Throwable $th) {
            return false;
        }
    }
}
