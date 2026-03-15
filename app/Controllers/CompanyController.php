<?php
namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\SectorModel;
use App\Models\CurrencyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;


class CompanyController extends BaseController
{
    public function index()
    {
        $companyModel = model(CompanyModel::class);
        $sectorModel = model(SectorModel::class);
        $countryModel = model(CountryModel::class);
        $exchangeModel = model(ExchangeModel::class);


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
            $builder = $builder->groupStart()
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
}