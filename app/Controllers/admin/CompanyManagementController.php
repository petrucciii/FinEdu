<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CountryModel;
use App\Models\SectorModel;
use App\Models\ExchangeModel;

class CompanyController extends BaseController
{
    private function isAdmin()
    {
        return $this->session->has('logged') && $this->session->get('role_id') == 1;
    }

    public function index()
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        //dictionaries
        $data = [
            'sectors' => model(SectorModel::class)->where('active', 1)->findAll(),
            'countries' => model(CountryModel::class)->where('active', 1)->findAll()
        ];

        echo view("templates/header");
        echo view("pages/viewCompanyList", $data);
        echo view("templates/footer");
    }

    //AJAX endpoiny
    public function search($query = '')
    {
        if (!$this->isAdmin())
            return $this->response->setStatusCode(403);

        $page = $this->request->getGet('page') ?? 1;
        $companyModel = model(CompanyModel::class);

        $result = $companyModel->searchAndPaginate(urldecode($query), $page);

        return $this->response->setJSON([
            'companies' => $result['companies'],
            'pagination' => [
                'currentPage' => $result['pager']->getCurrentPage(),
                'perPage' => $result['pager']->getPerPage(),
                'total' => $result['pager']->getTotal(),
                'pageCount' => $result['pager']->getPageCount()
            ]
        ]);
    }

    public function create()
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $companyModel = model(CompanyModel::class);

        $data = [
            'isin' => strtoupper(trim($this->request->getPost('isin'))),
            'name' => trim($this->request->getPost('name')),
            'ea_code' => $this->request->getPost('sector'),
            'country_code' => $this->request->getPost('country'),
            'id_user' => $this->session->get('user_id'),
            'active' => 1
        ];

        if ($companyModel->insert($data)) {
            return redirect()->to('/admin/CompanyController/edit/' . $data['isin'])->with('alert', "Azienda creata. Ora puoi completare il profilo.");
        }

        return redirect()->back()->with('alert', "Errore durante la creazione. Controlla che l'ISIN non esista già.");
    }

    public function edit($isin)
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $companyModel = model(CompanyModel::class);
        $company = $companyModel->getCompanyByISIN($isin);

        if (!$company)
            return redirect()->to('/admin/CompanyController/index')->with('alert', "Azienda non trovata");

        $data = [
            'company' => $company,
            'listings' => $companyModel->getListings($isin),
            'financials' => $companyModel->getFinancialData($isin),
            'board' => $companyModel->getBoardMembers($isin),
            'sectors' => model(SectorModel::class)->findAll(),
            'countries' => model(CountryModel::class)->findAll(),
            'exchanges' => model(ExchangeModel::class)->where('active', 1)->findAll()
        ];

        echo view("templates/header");
        echo view("pages/admins/viewCompanyEdit", $data);
        echo view("templates/footer");
    }

    public function update($isin)
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $companyModel = model(CompanyModel::class);
        $data = [
            'name' => trim($this->request->getPost('name')),
            'website' => trim($this->request->getPost('website')),
            'country_code' => $this->request->getPost('country'),
            'ea_code' => $this->request->getPost('sector'),
            'id_user' => $this->session->get('user_id')
        ];

        // Gestione logo se caricato
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'images/logos', $newName);
            $data['logo_path'] = '/images/logos/' . $newName;
        }

        $companyModel->update($isin, $data);
        return redirect()->back()->with('alert', "Dati azienda aggiornati con successo.");
    }

    public function delete($isin)
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $companyModel = model(CompanyModel::class);
        // Logical delete
        $companyModel->update($isin, ['active' => 0, 'id_user' => $this->session->get('user_id')]);

        return redirect()->to('/admin/CompanyController/index')->with('alert', 'Azienda eliminata (soft-delete).');
    }
}