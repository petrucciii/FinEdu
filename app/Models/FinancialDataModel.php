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
        'capex', 'dividends', 'id_user'
    ];

    public function findDataPerCompany(string $isin): array
    {
        $results = $this->where('isin', trim($isin))
                        ->orderBy('year', 'DESC')
                        ->findAll();

        $formatted = [];
        foreach ($results as $row) {
            //margins
            $row['ebit'] = (float)$row['net_profit'] + (float)$row['income_taxes'] + (float)$row['interests'];
            $row['tax_rate'] = $row['ebit'] != 0 ? ((float)$row['income_taxes'] / $row['ebit']) * 100 : 0;
            $row['net_margin'] = $row['revenues'] != 0 ? ((float)$row['net_profit'] / (float)$row['revenues']) * 100 : 0;
            
            //[year => record]
            $formatted[$row['year']] = $row;
        }

        return $formatted;
    }
}