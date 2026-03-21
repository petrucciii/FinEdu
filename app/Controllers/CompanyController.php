<?php
namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\SectorModel;
use App\Models\CurrencyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;
use App\Models\AnalystConsensusModel;
use App\Models\FinancialDataModel;
use App\Models\BoardModel;
use App\Models\ShareholderModel;


use App\Controllers\BaseController;

use Exception;

class CompanyController extends BaseController
{
    public function index()
    {
        echo view("templates/header");
        echo view("pages/viewCompanyList");
        echo view("templates/footer");

    }

    public function search($query = '')
    {
        $companyModel = model(CompanyModel::class);

        $page = $this->request->getGet('page') ?? 1;

        $builder = $companyModel;
        $builder = $builder
            ->join('sectors', "sectors.ea_code = companies.ea_code")
            ->join('countries', "countries.country_code = companies.country_code")
            ->join('listings', "listings.isin = companies.isin");

        $query = trim($query);
        if ($query != '') {
            $builder = $builder
                ->groupStart()
                ->like('companies.name', $query)
                ->orLike('companies.isin', $query)
                ->orLike('listings.ticker', $query)
                ->groupEnd();
        }

        $companies = $builder->paginate(10, 'default', $page);
        $pager = $companyModel->pager;

        return $this->response->setJSON([
            'companies' => $companies,
            'pagination' => [
                'currentPage' => $pager->getCurrentPage(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'pageCount' => $pager->getPageCount()
            ]
        ]);
    }

    public function viewCompany($isin){
        $companyModel = model(CompanyModel::class);
        $sectorModel = model(SectorModel::class);
        $countryModel = model(CountryModel::class);
        $exchangeModel = model(ExchangeModel::class);
        $consensusModel = model(AnalystConsensusModel::class);
        $consensusModel = model(AnalystConsensusModel::class);
        $financialDataModel = model(FinancialDataModel::class);
        $boardModel = model(BoardModel::class);
        $shareholderModel = model(ShareholderModel::class);


        try{
            $isin = trim($isin);
            $company = $companyModel->getCompanyByISIN($isin);
           

        } catch (Exception $e) {
            return redirect()->to('/CompanyController/index')->with('alert', 'Società non trovata');
        }

        $consensus = $consensusModel->findConsensusPerCompany($isin);
        $financialData = $financialDataModel->findDataPerCompany($isin);
        $board = $boardModel->findBoardPerCompany($isin);
        $shareholders = $shareholderModel->findShareholdersPerCompany($isin);
        

        $data = [
            'company' => $company[0],
            'consensus' => $consensus,
            'averageRating' => self::getAverageRating($consensus),
            'averageTargetPrice' => self::getAverageTargetPrice($consensus),
            'averageRating' => self::getAverageRating($consensus),
            'prices' => [],
            'news' => [],
            'financialData' => self::buildFinancialArray($financialData),
            'board' => $board,
            'shareholders' => $shareholders,
        ];

        echo "<pre>";
        print_r($data);
        echo "</pre>";


        echo view("templates/header");
        echo view("pages/viewCompany", $data);
        echo view("templates/footer");
        
    }

    private static function getAverageRating($consensus) {
        $ratings = [];

        foreach ($consensus as $c) {
            //ratings into numbers
            if($c['rating'] == "BUY" ) $rating = 1;
            else if($c['rating'] == "HOLD" ) $rating = 0;
            else $rating = -1;

            array_push($ratings, $rating);
        }

        $avgRating = array_sum($ratings) / count($ratings);
        

        if (round($avgRating) == 1) $avgRating = "BUY";
        else if (round($avgRating) == 0) $avgRating = "HOLD";
        else $avgRating = "SELL";

        return $avgRating;

    }
    
    private static function getAverageTargetPrice($consensus) {
        $targetPrices = [];

        foreach ($consensus as $c) {
            array_push($targetPrices, $c['target_price']);
        }

        $avgTP = array_sum($targetPrices) / count($targetPrices);
        
        return $avgTP;    
    }
    


    private static function buildFinancialArray($result){
        $labels = [
        'revenues'                    => 'Ricavi',
        'amortizations_depretiations' => 'Ammortamenti e Svalutazioni',
        'ebit'                        => 'EBIT (Risultato Operativo)',
        'interests'                   => 'Interessi',
        'income_taxes'                => 'Imposte sul Reddito',
        'net_profit'                  => 'Utile Netto',
        'net_margin'                  => 'Margine Netto (%)',
        'tax_rate'                    => 'Tax Rate (%)',
        'free_cash_flow'              => 'Free Cash Flow',
        'capex'                       => 'CAPEX',
        'dividends'                   => 'Dividendi',
        'net_debt'                    => 'Debito Netto',
        'share_number'                => 'Numero Azioni'
    ];

    $years = [];
    $rows = [];

    //base structure for data
    foreach ($labels as $key => $label) {
        $rows[$key] = [
            'label'  => $label,
            'values' => []
        ];
    }


    foreach ($result as $row) {
        //calculated data
        $ebit = (float)$row['net_profit'] + (float)$row['income_taxes'] + (float)$row['interests'];
        $tax_rate = $ebit != 0 ? ((float)$row['income_taxes'] / $ebit) * 100 : 0;
        $net_margin = $row['revenues'] != 0 ? ((float)$row['net_profit'] / (float)$row['revenues']) * 100 : 0;

        $row['ebit']       = $ebit;
        $row['tax_rate']   = $tax_rate;
        $row['net_margin'] = $net_margin;

        //headers
        $yearKey = $row['year'];
        $years[$yearKey] = $yearKey . " " . $row['type'];

        //year foreach key data
        foreach ($labels as $key => $label) {
            $rows[$key]['values'][$yearKey] = $row[$key] ?? 0; 
        }
    }

    return [
        'years' => $years,
        'rows'  => $rows, // ['net_profit' => ["label" => "utile netto", "values" => [ 2022 => 10000, 2023 => 400000] ] ]
        'currency_code' => $result[0]['currency_code']
    ];
    }
}