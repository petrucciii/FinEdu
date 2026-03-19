<?php
namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\SectorModel;
use App\Models\CurrencyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;
use App\Models\AnalystConsensusModel;
use App\Models\FinancialDataModel;

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


        try{
            $isin = trim($isin);
            $company = $companyModel->fread(['isin' => $isin]);
           

        } catch (Exception $e) {
            return redirect()->to('/CompanyController/index')->with('alert', 'Società non trovata');
        }

         $data = [
                'company' => $company[0],
                'consensus' => $consensusModel->findConsensusPerCompany($isin),
                'prices' => [],
                'news' => [],
                'financial_data' => $financialDataModel->findDataPerCompany($isin),
                'board' => [],
                'shareholders' => []
            ];


        echo view("templates/header");
        echo view("pages/viewCompany", $data);
        echo view("templates/footer");
        
    }
}