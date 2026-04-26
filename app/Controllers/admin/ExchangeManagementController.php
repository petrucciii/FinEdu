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

    //load pagina
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $ex = model(ExchangeModel::class)->fread();
        $countries = model(CountryModel::class)->fread();
        $currencies = model(CurrencyModel::class)->fread();
        $data = [
            'exchanges' => is_array($ex) ? $ex : [],
            'countries' => is_array($countries) ? $countries : [],
            'currencies' => is_array($currencies) ? $currencies : [],
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewExchangeManagement', $data);
        echo view('templates/footer');
    }

    //aggiunta nuovo exchange
    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $model = model(ExchangeModel::class);

        $data = [
            'mic' => strtoupper(trim((string) $this->request->getPost('mic'))),
            'short_name' => trim((string) $this->request->getPost('short_name')),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'country_code' => $this->request->getPost('country_code'),
            'currency_code' => $this->request->getPost('currency_code'),
            'opening_hour' => $this->request->getPost('opening_hour') ?: null,
            'closing_hour' => $this->request->getPost('closing_hour') ?: null,
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ];

        if ($model->insert($data)) {
            return redirect()->back()->with('alert', 'Borsa inserita con successo!');
        }

        return redirect()->back()->with('alert', 'Errore: controlla i dati e il codice MIC.');
    }

    //modifica nuovo exchange
    public function update()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $mic = strtoupper(trim((string) $this->request->getPost('mic')));
        if ($mic === '') {
            return redirect()->back()->with('alert', 'MIC mancante.');
        }

        $data = [
            'short_name' => trim((string) $this->request->getPost('short_name')),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'country_code' => $this->request->getPost('country_code'),
            'currency_code' => $this->request->getPost('currency_code'),
            'opening_hour' => $this->request->getPost('opening_hour') ?: null,
            'closing_hour' => $this->request->getPost('closing_hour') ?: null,
            'id_user' => $this->session->get('user_id'),
        ];

        if (model(ExchangeModel::class)->fupdate($mic, $data)) {
            return redirect()->back()->with('alert', 'Borsa aggiornata.');
        }

        return redirect()->back()->with('alert', 'Aggiornamento non riuscito.');
    }

    //eliminazione exchange (soft)
    public function delete()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $mic = strtoupper(trim((string) $this->request->getPost('mic')));
        if ($mic === '') {
            return redirect()->back()->with('alert', 'MIC mancante.');
        }

        if (model(ExchangeModel::class)->fdelete($mic)) {
            return redirect()->back()->with('alert', 'Borsa disattivata.');
        }

        return redirect()->back()->with('alert', 'Operazione non riuscita.');
    }
}
