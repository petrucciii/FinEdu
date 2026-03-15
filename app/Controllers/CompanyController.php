<?php
namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\SectorsModel;

use App\Models\CountriesModel;


class CompanyController extends BaseController
{
    public function index()
    {
        $model = model(CompanyModel::class);
        echo view("templates/header");
        echo view("pages/viewCompanyList");
        echo view("templates/footer");

    }
}