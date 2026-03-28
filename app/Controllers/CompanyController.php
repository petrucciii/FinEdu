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
use Throwable;

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

        //get page number by get method, if not present set it to 1
        $page = $this->request->getGet('page') ?? 1;

        //call model method to handle search and pagination
        $result = $companyModel->searchAndPaginate($query, $page);

        $companies = $result['companies'];
        $pager = $result['pager'];

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

    public function viewCompany($isin)
    {
        $companyModel = model(CompanyModel::class);
        $consensusModel = model(AnalystConsensusModel::class);
        $consensusModel = model(AnalystConsensusModel::class);
        $financialDataModel = model(FinancialDataModel::class);
        $boardModel = model(BoardModel::class);
        $shareholderModel = model(ShareholderModel::class);


        try {
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
            'company' => $company,
            'consensus' => $consensus,
            'averageRating' => self::getAverageRating($consensus),
            'averageTargetPrice' => self::getAverageTargetPrice($consensus),
            'prices' => [],
            'news' => [],
            'financialData' => self::buildFinancialArray($financialData),
            'board' => $board,
            'shareholders' => $shareholders,
        ];


        echo view("templates/header");
        echo view("pages/viewCompany", $data);
        echo view("templates/footer");
    }

    private static function getAverageRating($consensus)
    {
        $ratings = [];

        foreach ($consensus as $c) {
            //ratings into numbers
            if ($c['rating'] == "BUY")
                $rating = 1;
            else if ($c['rating'] == "HOLD")
                $rating = 0;
            else
                $rating = -1;

            array_push($ratings, $rating);
        }

        try {
            $avgRating = array_sum($ratings) / count($ratings);


            if (round($avgRating) == 1)
                $avgRating = "BUY";
            else if (round($avgRating) == 0)
                $avgRating = "HOLD";
            else
                $avgRating = "SELL";

            return $avgRating;
        } catch (Throwable $e) {
            return "N/A";
        }
    }

    private static function getAverageTargetPrice($consensus)
    {
        $targetPrices = [];

        foreach ($consensus as $c) {
            array_push($targetPrices, $c['target_price']);
        }

        try {
            $avgTP = array_sum($targetPrices) / count($targetPrices);
            return $avgTP;
        } catch (Throwable $e) {
            return "N/A";
        }
    }


    private static function buildFinancialArray($result)
    {
        $labels = [
            'revenues' => 'Ricavi',
            'amortizations_depretiations' => 'Ammortamenti e Svalutazioni',
            'ebit' => 'EBIT (Risultato Operativo)',
            'interests' => 'Interessi',
            'income_taxes' => 'Imposte sul Reddito',
            'net_profit' => 'Utile Netto',
            'net_margin' => 'Margine Netto (%)',
            'tax_rate' => 'Tax Rate (%)',
            'free_cash_flow' => 'Free Cash Flow',
            'capex' => 'CAPEX',
            'dividends' => 'Dividendi',
            'net_debt' => 'Debito Netto',
            'share_number' => 'Numero Azioni'
        ];

        $years = [];
        $rows = [];

        // base structure for views
        foreach ($labels as $key => $label) {
            $rows[$key] = [
                'label' => $label,
                'values' => []
            ];
        }

        if (empty($result) || !is_array($result)) {
            return false;
        }

        $firstRow = reset($result);
        $currencyCode = $firstRow['currency_code'] ?? 'N/A';

        foreach ($result as $row) {
            //if null -> -
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $row[$key] = '-';
                }
            }

            if (!isset($row['year']) || !isset($row['type'])) {
                return false;
            }

            $net_profit = (float) ($row['net_profit'] ?? 0);
            $income_taxes = (float) ($row['income_taxes'] ?? 0);
            $interests = (float) ($row['interests'] ?? 0);
            $revenues = (float) ($row['revenues'] ?? 0);

            $ebit = $net_profit + $income_taxes + $interests;

            // use '-' if the condition isn't met
            $tax_rate = $ebit > 0 ? ($income_taxes / $ebit) * 100 : '-';
            $net_margin = $revenues != 0 ? ($net_profit / $revenues) * 100 : '-';

            $row['ebit'] = $ebit;
            $row['tax_rate'] = $tax_rate;
            $row['net_margin'] = $net_margin;

            $yearKey = $row['year'];
            $type = $row['type'];
            $years[$yearKey] = trim($yearKey . " " . $type);

            foreach ($labels as $key => $label) {
                $rows[$key]['values'][$yearKey] = $row[$key] ?? '-';
            }
        }

        return [
            'years' => $years,
            'rows' => $rows,
            'currency_code' => $currencyCode
        ];
    }
}