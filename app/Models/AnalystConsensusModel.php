<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class AnalystConsensusModel extends Model
{
    protected $table = 'analysts_consensus';
    protected $primaryKey = 'analysis_id';
    protected $allowedFields = [
        'isin',
        'firm_id',
        'date',
        'rating_id',
        'target_price'
    ];


    public function findConsensusPerCompany($isin)
    {
        return $this->select('analysts_consensus.isin, analysts_consensus.date, firms.firm_name, ratings.rating, analysts_consensus.target_price')
                    ->join('firms', 'firms.firm_id = analysts_consensus.firm_id')
                    ->join('ratings', 'ratings.rating_id = analysts_consensus.rating_id')
                    ->where('analysts_consensus.isin', trim($isin))
                    ->orderBy('analysts_consensus.date', 'DESC')
                    ->findAll();
    }

}
