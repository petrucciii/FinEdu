<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialDataModel extends Model
{
    protected $table      = 'data';
    //ci4 doesn't support array as pks so delete and update will be managed "manually"
    protected $primaryKey = 'isin'; 

    protected $allowedFields = [
        'year', 'isin', 'type_id', 'currency_code', 'revenues', 
        'amortizations_depretiations', 'income_taxes', 'interests', 
        'net_profit', 'net_debt', 'share_number', 'free_cash_flow', 
        'capex', 'dividends', 'id_ser'
    ];

    public function findDataPerCompany(string $isin): array
    {
        $result = $this->select('data.*, data_type.type')
                        ->where('isin', trim($isin))
                        ->join('data_type', 'data_type.type_id = data.type_id')
                        ->orderBy('year', 'DESC')
                        ->findAll();
        return $result;
    }

}