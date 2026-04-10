<?php

namespace App\Models;

use CodeIgniter\Model;

//model della tabella 'analysts_consensus' 
class AnalystConsensusModel extends Model
{
    protected $table = 'analysts_consensus';
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

    //timestamp automatici su insert/update
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    //elenco consensus per società
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

    //insert con querybuilder
    public function insertRow(array $row): bool
    {
        try {
            return $this->insert($row);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //aggiornamento per chiave primaria analysis_id
    public function updateRow(int $analysisId, array $row): bool
    {
        try {
            return $this->update($analysisId, $row);
        } catch (\Throwable $th) {
            return false;
        }

    }

    //soft delete
    public function deleteRow(int $analysisId): bool
    {
        try {
            return $this->update($analysisId, ['active' => 0]);

        } catch (\Throwable $th) {
            return false;
        }
    }
}
