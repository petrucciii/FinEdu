<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExchangeModel;
use App\Models\CountryModel;
use App\Models\CurrencyModel;

class ExchangeManagementController extends BaseController
{
    private function isAdmin()
    {
        return $this->session->has('logged') && $this->session->get('role_id') == 1;
    }

    public function index()
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $data = [
            'exchanges' => model(ExchangeModel::class)->where('active', 1)->findAll(),
            'countries' => model(CountryModel::class)->where('active', 1)->findAll(),
            'currencies' => model(CurrencyModel::class)->where('active', 1)->findAll()
        ];

        echo view("templates/header");
        echo view("pages/admins/viewExchangeManagement", $data);
        echo view("templates/footer");
    }

    public function create()
    {
        if (!$this->isAdmin())
            return redirect()->to('/');

        $model = model(ExchangeModel::class);

        $data = [
            'mic' => strtoupper(trim($this->request->getPost('mic'))),
            'short_name' => trim($this->request->getPost('short_name')),
            'full_name' => trim($this->request->getPost('full_name')),
            'country_code' => $this->request->getPost('country_code'),
            'currency_code' => $this->request->getPost('currency_code'),
            'opening_hour' => $this->request->getPost('opening_hour'),
            'closing_hour' => $this->request->getPost('closing_hour'),
            'id_user' => $this->session->get('user_id')
        ];

        if ($model->insert($data)) {
            return redirect()->back()->with('alert', 'Borsa inserita con successo!');
        }

        return redirect()->back()->with('alert', 'Errore: controlla i dati e il codice MIC.');
    }
}