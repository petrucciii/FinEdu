<?php

namespace App\Models;

use CodeIgniter\Model;

//i Model tabella `analysts_consensus` (consensus analisti per ISIN)
class AnalystConsensusModel extends Model
{
    protected $table      = 'analysts_consensus';
    protected $primaryKey = 'analysis_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'isin',
        'firm_id',
        'date',
        'rating_id',
        'target_price',
        'id_user',
        'active',
    ];

    //i timestamp automatici su insert/update tramite Model (se usati)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //i elenco consensus per scheda società (vista pubblica/management)
    public function findConsensusPerCompany($isin)
    {
        return $this->select('analysts_consensus.analysis_id, analysts_consensus.isin, analysts_consensus.date, analysts_consensus.firm_id, analysts_consensus.rating_id, analysts_consensus.target_price, firms.firm_name, ratings.rating')
            ->join('firms', 'firms.firm_id = analysts_consensus.firm_id')
            ->join('ratings', 'ratings.rating_id = analysts_consensus.rating_id')
            ->where('analysts_consensus.isin', trim($isin))
            ->where('analysts_consensus.active', 1)
            ->orderBy('analysts_consensus.date', 'DESC')
            ->findAll();
    }

    //i inserimento riga consensus via Query Builder (campi espliciti)
    public function insertRow(array $row): bool
    {
        return $this->db->table($this->table)->insert($row);
    }

    //i aggiornamento per chiave primaria analysis_id
    public function updateRow(int $analysisId, array $row): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('analysis_id', $analysisId)
            ->update($row);
    }

    //i eliminazione fisica (admin)
    public function deleteRow(int $analysisId): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('analysis_id', $analysisId)
            ->delete();
    }
}
