<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BoardModel;
use App\Models\CompanyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;
use App\Models\FinancialDataModel;
use App\Models\ListingModel;
use App\Models\SectorModel;
use App\Models\ShareholderModel;

class CompanyManagementController extends BaseController
{
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $data = [
            'sectors'      => model(SectorModel::class)->where('active', 1)->findAll(),
            'countries'    => model(CountryModel::class)->where('active', 1)->findAll(),
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/viewCompanyList', $data);
        echo view('templates/footer');
    }

    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403);
        }

        $page         = $this->request->getGet('page') ?? 1;
        $companyModel = model(CompanyModel::class);
        $result       = $companyModel->searchAndPaginate(urldecode($query), $page);

        return $this->response->setJSON([
            'companies'    => $result['companies'],
            'pagination'   => [
                'currentPage' => $result['pager']->getCurrentPage(),
                'perPage'     => $result['pager']->getPerPage(),
                'total'       => $result['pager']->getTotal(),
                'pageCount'   => $result['pager']->getPageCount(),
            ],
        ]);
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $data         = [
            'isin'         => strtoupper(trim((string) $this->request->getPost('isin'))),
            'name'         => trim((string) $this->request->getPost('name')),
            'ea_code'      => $this->request->getPost('sector'),
            'country_code' => $this->request->getPost('country'),
            'id_user'      => $this->session->get('user_id'),
            'active'       => 1,
        ];

        if ($companyModel->insert($data)) {
            return redirect()->to('/admin/CompanyManagementController/edit/' . $data['isin'])->with('alert', 'Azienda creata. Ora puoi completare il profilo.');
        }

        return redirect()->back()->with('alert', "Errore. Controlla che l'ISIN non esista già.")->with('alert_type', 'danger');
    }

    public function edit($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $company      = $companyModel->getCompanyByISIN($isin);

        if (!$company) {
            return redirect()->to('/admin/CompanyManagementController/index')->with('alert', 'Azienda non trovata')->with('alert_type', 'danger');
        }

        $db = \Config\Database::connect();

        $data = [
            'company'       => $company,
            'sectors'       => model(SectorModel::class)->findAll(),
            'countries'     => model(CountryModel::class)->findAll(),
            'exchanges'     => model(ExchangeModel::class)->where('active', 1)->findAll(),
            'currencies'    => $db->table('currencies')->get()->getResultArray(),
            'data_types'    => $db->table('data_type')->where('active', 1)->get()->getResultArray(),
            'all_firms'     => $db->table('firms')->where('active', 1)->orderBy('firm_name', 'ASC')->get()->getResultArray(),
            'all_members'   => $db->table('board_members')->where('active', 1)->orderBy('full_name', 'ASC')->get()->getResultArray(),
            'listings'      => model(ListingModel::class)->findActiveByIsin($isin),
            'financials'    => model(FinancialDataModel::class)->findDataPerCompany($isin),
            'board'         => model(BoardModel::class)->findBoardPerCompany($isin),
            'shareholders'  => model(ShareholderModel::class)->findShareholdersPerCompany($isin),
            'adminSection'  => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewCompanyManagement', $data);
        echo view('templates/footer');
    }

    public function update($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $data         = [
            'name'          => trim((string) $this->request->getPost('name')),
            'website'       => trim((string) $this->request->getPost('website')),
            'country_code'  => $this->request->getPost('country'),
            'ea_code'       => $this->request->getPost('sector'),
            'main_exchange' => trim((string) $this->request->getPost('main_exchange')),
            'id_user'       => $this->session->get('user_id'),
        ];

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName            = $file->getRandomName();
            $file->move(FCPATH . 'images/logos', $newName);
            $data['logo_path'] = '/images/logos/' . $newName;
        }

        $companyModel->update($isin, $data);

        return redirect()->back()->with('alert', 'Dati base aggiornati con successo.');
    }

    public function delete($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }
        model(CompanyModel::class)->update($isin, ['active' => 0, 'id_user' => $this->session->get('user_id')]);

        return redirect()->to('/admin/CompanyManagementController/index')->with('alert', 'Azienda disattivata.');
    }

    public function addListing()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $ok = model(ListingModel::class)->insertRow([
            'ticker'   => strtoupper(trim((string) $this->request->getPost('ticker'))),
            'mic'      => trim((string) $this->request->getPost('mic')),
            'isin'     => trim((string) $this->request->getPost('isin')),
            'id_user'  => $this->session->get('user_id'),
            'active'   => 1,
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Quotazione aggiunta con successo.')
            : redirect()->back()->with('alert', 'Impossibile aggiungere la quotazione (verifica ticker/MIC o vincoli DB).')->with('alert_type', 'danger');
    }

    public function deleteListing($ticker, $mic)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(ListingModel::class)->deleteRow(rawurldecode($ticker), rawurldecode($mic));

        return redirect()->back()->with('alert', 'Quotazione rimossa.');
    }

    public function saveFinancial()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $model  = model(FinancialDataModel::class);
        $isin   = trim((string) $this->request->getPost('isin'));
        $isEdit = $this->request->getPost('is_edit') === '1';
        $year   = (int) $this->request->getPost('year');

        if ($year < 1900 || $year > 2100) {
            return redirect()->back()->with('alert', 'Anno non valido.')->with('alert_type', 'danger');
        }

        $payload = $this->buildFinancialPayload();

        if ($isEdit) {
            $ok  = $model->updateRow($isin, $year, $payload);
            $msg = $ok ? "Bilancio {$year} aggiornato." : "Aggiornamento non riuscito.";
        } else {
            $insert = array_merge($payload, [
                'year'  => $year,
                'isin'  => $isin,
            ]);
            $ok  = $model->insertRow($insert);
            $msg = $ok ? "Bilancio {$year} inserito." : "Inserimento non riuscito (anno già presente o dati non validi).";
        }

        return $ok
            ? redirect()->back()->with('alert', $msg)
            : redirect()->back()->with('alert', $msg)->with('alert_type', 'danger');
    }

    public function deleteFinancial($year, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(FinancialDataModel::class)->deleteRow(rawurldecode($isin), (int) $year);

        return redirect()->back()->with('alert', 'Bilancio rimosso.');
    }

    public function addBoardMember()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin      = trim((string) $this->request->getPost('isin'));
        $memberId  = (int) $this->request->getPost('member_id');
        $role      = trim((string) $this->request->getPost('role'));
        $idUser    = $this->session->get('user_id');

        if ($role === '' || $memberId < 1) {
            return redirect()->back()->with('alert', 'Ruolo e membro sono obbligatori.')->with('alert_type', 'danger');
        }

        $ok = model(BoardModel::class)->insertRow([
            'isin'      => $isin,
            'member_id' => $memberId,
            'role'      => $role,
            'id_user'   => $idUser,
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Membro associato al CdA.')
            : redirect()->back()->with('alert', 'Impossibile aggiungere il membro (già presente o errore DB).')->with('alert_type', 'danger');
    }

    public function updateBoardMember()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin     = trim((string) $this->request->getPost('isin'));
        $memberId = (int) $this->request->getPost('member_id');
        $role     = trim((string) $this->request->getPost('role'));

        if ($role === '') {
            return redirect()->back()->with('alert', 'Il ruolo non può essere vuoto.')->with('alert_type', 'danger');
        }

        $ok = model(BoardModel::class)->updateRow($isin, $memberId, [
            'role'        => $role,
            'id_user'     => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Ruolo aggiornato.')
            : redirect()->back()->with('alert', 'Aggiornamento ruolo non riuscito.')->with('alert_type', 'danger');
    }

    public function deleteBoardMember($member_id, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(BoardModel::class)->deleteRow(rawurldecode($isin), (int) $member_id);

        return redirect()->back()->with('alert', 'Membro rimosso dal CdA.');
    }

    public function addShareholder()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin       = trim((string) $this->request->getPost('isin'));
        $firmId     = (int) $this->request->getPost('firm_id');
        $ownership  = $this->request->getPost('ownership');

        if ($firmId < 1 || $ownership === '' || $ownership === null) {
            return redirect()->back()->with('alert', 'Fondo e quota sono obbligatori.')->with('alert_type', 'danger');
        }

        $ok = model(ShareholderModel::class)->insertRow([
            'isin'      => $isin,
            'firm_id'   => $firmId,
            'ownership' => (float) $ownership,
            'id_user'   => $this->session->get('user_id'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Azionista associato.')
            : redirect()->back()->with('alert', 'Impossibile aggiungere l\'azionista (già presente o dati non validi).')->with('alert_type', 'danger');
    }

    public function updateShareholder()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin      = trim((string) $this->request->getPost('isin'));
        $firmId    = (int) $this->request->getPost('firm_id');
        $ownership = $this->request->getPost('ownership');

        if ($ownership === '' || $ownership === null) {
            return redirect()->back()->with('alert', 'Quota non valida.')->with('alert_type', 'danger');
        }

        $ok = model(ShareholderModel::class)->updateRow($isin, $firmId, [
            'ownership'   => (float) $ownership,
            'id_user'     => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Quota aggiornata.')
            : redirect()->back()->with('alert', 'Aggiornamento quota non riuscito.')->with('alert_type', 'danger');
    }

    public function deleteShareholder($firm_id, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(ShareholderModel::class)->deleteRow(rawurldecode($isin), (int) $firm_id);

        return redirect()->back()->with('alert', 'Azionista rimosso.');
    }

    /**
     * @return array<string, int|string|null>
     */
    private function buildFinancialPayload(): array
    {
        $typeId = (int) $this->request->getPost('type_id');
        $cur    = trim((string) $this->request->getPost('currency_code'));

        $row = [
            'type_id'       => $typeId,
            'currency_code' => $cur,
            'id_user'       => $this->session->get('user_id'),
            'last_update'   => date('Y-m-d H:i:s'),
        ];

        foreach (FinancialDataModel::BIGINT_FIELDS as $field) {
            $v = $this->request->getPost($field);
            $row[$field] = ($v === '' || $v === null) ? null : (int) $v;
        }

        return $row;
    }
}
