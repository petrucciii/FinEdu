<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnalystConsensusModel;
use App\Models\BoardModel;
use App\Models\CompanyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;
use App\Models\FinancialDataModel;
use App\Models\ListingModel;
use App\Models\SectorModel;
use App\Models\ShareholderModel;
use Exception;

class CompanyManagementController extends BaseController
{

    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    //legge il parametro active_tab dal POST per mantenere il focus sulla tab corrente dopo il redirect
    private function getActiveTab(string $default = 'info'): string
    {
        $tab = trim((string) $this->request->getPost('active_tab'));
        return !empty($tab) ? $tab : $default;
    }

    //etichette bilanci allineate a CompanyController::buildFinancialArray (stessi testi)
    public static function financialLabelsForManagement(): array
    {
        return [
            'revenues' => 'Ricavi',
            'amortizations_depretiations' => 'Ammortamenti e Svalutazioni',
            'income_taxes' => 'Imposte sul Reddito',
            'interests' => 'Interessi',
            'net_profit' => 'Utile Netto',
            'net_debt' => 'Debito Netto',
            'share_number' => 'Numero Azioni',
            'free_cash_flow' => 'Free Cash Flow',
            'capex' => 'CAPEX',
            'dividends' => 'Dividendi',
        ];
    }

    //
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $data = [
            'sectors' => model(SectorModel::class)->where('active', 1)->findAll(),
            'countries' => model(CountryModel::class)->where('active', 1)->findAll(),
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/viewCompanyList', $data);
        echo view('templates/footer');
    }

    //endpoint AJAX
    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            //403 Forbidden se richiesta non è autorizzata (non è admin)
            return $this->response->setStatusCode(403);
        }

        $page = $this->request->getGet('page') ?? 1;
        $companyModel = model(CompanyModel::class);
        //impaginazione e ricerca aziende con query recuperata da 
        $result = $companyModel->searchAndPaginate(urldecode($query), $page);

        return $this->response->setJSON([
            'companies' => $result['companies'],
            'pagination' => [
                'currentPage' => $result['pager']->getCurrentPage(),
                'perPage' => $result['pager']->getPerPage(),
                'total' => $result['pager']->getTotal(),
                'pageCount' => $result['pager']->getPageCount(),
            ],
        ]);
    }

    //crea nuova company
    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $data = [
            'isin' => strtoupper(trim((string) $this->request->getPost('isin'))),
            'name' => trim((string) $this->request->getPost('name')),
            'ea_code' => $this->request->getPost('sector'),
            'country_code' => $this->request->getPost('country'),
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ];

        if ($companyModel->insert($data)) {
            return redirect()->to('/admin/CompanyManagementController/edit/' . $data['isin'])->with('alert', 'Azienda creata. Ora puoi completare il profilo.');
        }

        return redirect()->back()->with('alert', "Errore. Controlla che l'ISIN non esista già.")->with('alert_type', 'danger');
    }

    //load pagina amministrazione (admin)
    public function edit($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $company = $companyModel->getCompanyByISIN($isin);

        if (!$company) {
            return redirect()->to('/admin/CompanyManagementController/index')->with('alert', 'Azienda non trovata')->with('alert_type', 'danger');
        }

        $db = db_connect();//da cambiare, usare i model

        $data = [
            'company' => $company,
            'sectors' => model(SectorModel::class)->findAll(),
            'countries' => model(CountryModel::class)->findAll(),
            'exchanges' => model(ExchangeModel::class)->where('active', 1)->findAll(),
            'currencies' => $db->table('currencies')->get()->getResultArray(),
            'data_types' => $db->table('data_type')->where('active', 1)->get()->getResultArray(),
            'all_firms' => $db->table('firms')->where('active', 1)->orderBy('firm_name', 'ASC')->get()->getResultArray(),
            'all_members' => $db->table('board_members')->where('active', 1)->orderBy('full_name', 'ASC')->get()->getResultArray(),
            'ratings' => $db->table('ratings')->where('active', 1)->orderBy('rating', 'ASC')->get()->getResultArray(),
            'listings' => model(ListingModel::class)->findActiveByIsin($isin),
            'financials' => model(FinancialDataModel::class)->findDataPerCompany($isin),
            'board' => model(BoardModel::class)->findBoardPerCompany($isin),
            'shareholders' => model(ShareholderModel::class)->findShareholdersPerCompany($isin),
            'consensus' => model(AnalystConsensusModel::class)->findConsensusPerCompany($isin),
            'financialLabels' => self::financialLabelsForManagement(),
            'boardMemberCreateUrl' => base_url('admin/BoardMembersController/create'),
            'adminSection' => true,
            'activeTab' => session()->getFlashdata('tab') ?? 'info',
        ];

        echo view('templates/header');
        echo view('pages/admins/viewCompanyManagement', $data);
        echo view('templates/footer');
    }

    //update company
    public function update($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $companyModel = model(CompanyModel::class);
        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'website' => trim((string) $this->request->getPost('website')),
            'country_code' => $this->request->getPost('country'),
            'ea_code' => $this->request->getPost('sector'),
            'main_exchange' => trim((string) $this->request->getPost('main_exchange')),
            'id_user' => $this->session->get('user_id'),
        ];

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();//nome casuale per il file dato che quello presente non viene eliminato
            $file->move(FCPATH . 'images/logos', $newName);//sposta il file nella cartella images/logos
            $data['logo_path'] = '/images/logos/' . $newName;//salva il percorso del file
        }

        $companyModel->update($isin, $data);

        return redirect()->back()->with('alert', 'Dati base aggiornati con successo.');
    }


    public function delete($isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }
        model(CompanyModel::class)->deleteCompany($isin, ['id_user' => $this->session->get('user_id')]);

        return redirect()->to('/admin/CompanyManagementController/index')->with('alert', 'Azienda disattivata.');
    }

    //aggiunge listing
    public function addListing()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $ok = model(ListingModel::class)->insertRow([
            'ticker' => strtoupper(trim((string) $this->request->getPost('ticker'))),
            'mic' => trim((string) $this->request->getPost('mic')),
            'isin' => trim((string) $this->request->getPost('isin')),
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ]);

        return redirect()->back()->with('alert', 'Quotazione aggiunta con successo.');
    }

    //elimina listing
    public function deleteListing($ticker, $mic)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $ticker = trim(rawurldecode($ticker));
        $mic = trim(rawurldecode($mic));

        if (model(ListingModel::class)->deleteRow($ticker, $mic)) {
            return redirect()->back()->with('alert', 'Quotazione rimossa.')->with('tab', 'listings');
        }

        return redirect()->back()->with('alert', 'Impossibile rimuovere la quotazione.')->with('alert_type', 'danger')->with('tab', 'listings');
    }

    //import bilanci da file XML (standard progetto): upsert per anno su tabella data
    /**
     * NON FUNZIONANTE
     */
    public function importFinancialXml()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin = trim((string) $this->request->getPost('isin'));
        $typeId = (int) $this->request->getPost('type_id');
        $currency = trim((string) $this->request->getPost('currency_code'));
        $file = $this->request->getFile('xml_file');

        if ($isin === '' || $typeId < 1 || $currency === '') {
            return redirect()->back()->with('alert', 'Tipo dato, valuta e ISIN sono obbligatori per l\'import.')->with('alert_type', 'danger')->with('tab', 'financials');
        }

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('alert', 'File XML non valido o mancante.')->with('alert_type', 'danger')->with('tab', 'financials');
        }

        //cartella scrittura per upload temporanei XML
        $uploadDir = WRITEPATH . 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmp = $file->getRandomName();
        $file->move($uploadDir, $tmp);
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $tmp;

        try {
            $parsed = $this->parseFinancialStatementsFromFile($fullPath);
        } catch (Exception $e) {
            @unlink($fullPath);

            return redirect()->back()->with('alert', 'XML: ' . $e->getMessage())->with('alert_type', 'danger');
        }

        @unlink($fullPath);

        if ($parsed === []) {
            return redirect()->back()->with('alert', 'Nessun anno ricavato dall\'XML.')->with('alert_type', 'danger');
        }

        $finModel = model(FinancialDataModel::class);
        $userId = $this->session->get('user_id');
        $inserted = 0;
        $updated = 0;

        foreach ($parsed as $year => $cols) {
            $yearInt = (int) $year;
            if ($yearInt < 1900 || $yearInt > 2100) {
                continue;
            }

            $payload = array_merge($cols, [
                'type_id' => $typeId,
                'currency_code' => $currency,
                'id_user' => $userId,
                'last_update' => date('Y-m-d H:i:s'),
            ]);

            if ($finModel->hasYear($isin, $yearInt)) {
                if ($finModel->updateRow($isin, $yearInt, $payload)) {
                    $updated++;
                }
            } else {
                $payload['year'] = $yearInt;
                $payload['isin'] = $isin;
                if ($finModel->insertRow($payload)) {
                    $inserted++;
                }
            }
        }

        return redirect()->back()->with('alert', "Import XML completato: {$inserted} inserimenti, {$updated} aggiornamenti.");
    }

    //aggiorna o aggiunge riga bilancio
    public function saveFinancial()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $model = model(FinancialDataModel::class);
        $isin = trim((string) $this->request->getPost('isin'));
        //se salva è stato fatto da una riga esistente
        $isEdit = $this->request->getPost('is_edit') === '1';
        $year = (int) $this->request->getPost('year');
        $tab = $this->getActiveTab('financials');

        if ($year < 1900 || $year > 2100) {
            return redirect()->back()->with('alert', 'Anno non valido.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $payload = $this->buildFinancialPayload();

        //aggiorna o inserisce riga
        if ($isEdit) {
            $ok = $model->updateRow($isin, $year, $payload);
            $msg = $ok ? "Bilancio {$year} aggiornato." : 'Aggiornamento non riuscito.';
        } else {
            $insert = array_merge($payload, [
                'year' => $year,
                'isin' => $isin,
            ]);
            $ok = $model->insertRow($insert);
            $msg = $ok ? "Bilancio {$year} inserito." : 'Inserimento non riuscito (anno già presente o dati non validi).';
        }

        return $ok
            ? redirect()->back()->with('alert', $msg)->with('tab', $tab)
            : redirect()->back()->with('alert', $msg)->with('alert_type', 'danger')->with('tab', $tab);
    }

    //elimina bilancio
    public function deleteFinancial($year, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(FinancialDataModel::class)->deleteRow(rawurldecode($isin), (int) $year);

        return redirect()->back()->with('alert', 'Bilancio rimosso.')->with('tab', 'financials');
    }

    //aggiunge membro cda di una societa
    public function addBoardMember()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $rawMember = $this->request->getPost('member_id');

        $isin = trim((string) $this->request->getPost('isin'));
        $memberId = (int) $rawMember;
        $role = trim((string) $this->request->getPost('role'));
        $idUser = $this->session->get('user_id');
        $tab = $this->getActiveTab('board');

        if ($role === '' || $memberId < 1) {
            return redirect()->back()->with('alert', 'Ruolo e membro sono obbligatori.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(BoardModel::class)->insertRow([
            'isin' => $isin,
            'member_id' => $memberId,
            'role' => $role,
            'id_user' => $idUser,
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Membro associato al CdA.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Impossibile aggiungere il membro (già presente o errore DB).')->with('alert_type', 'danger')->with('tab', $tab);
    }

    //update membro board
    public function updateBoardMember()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin = trim((string) $this->request->getPost('isin'));
        $memberId = (int) $this->request->getPost('member_id');
        $role = trim((string) $this->request->getPost('role'));
        $tab = $this->getActiveTab('board');

        if ($role === '') {
            return redirect()->back()->with('alert', 'Il ruolo non può essere vuoto.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(BoardModel::class)->updateRow($isin, $memberId, [
            'role' => $role,
            'id_user' => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Ruolo aggiornato.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Aggiornamento ruolo non riuscito.')->with('alert_type', 'danger')->with('tab', $tab);
    }

    //elimina membro del board
    public function deleteBoardMember($member_id, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(BoardModel::class)->deleteRow(rawurldecode($isin), (int) $member_id);

        return redirect()->back()->with('alert', 'Membro rimosso dal CdA.')->with('tab', 'board');
    }

    //aggiunge azionista
    public function addShareholder()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin = trim((string) $this->request->getPost('isin'));
        $firmId = (int) $this->request->getPost('firm_id');
        $ownership = $this->request->getPost('ownership');
        $tab = $this->getActiveTab('shareholders');

        if ($firmId < 1 || $ownership === '' || $ownership === null) {
            return redirect()->back()->with('alert', 'Fondo e quota sono obbligatori.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(ShareholderModel::class)->insertRow([
            'isin' => $isin,
            'firm_id' => $firmId,
            'ownership' => (float) $ownership,
            'id_user' => $this->session->get('user_id'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Azionista associato.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Impossibile aggiungere l\'azionista (già presente o dati non validi).')->with('alert_type', 'danger')->with('tab', $tab);
    }

    //aggiorna ownership shareholder
    public function updateShareholder()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin = trim((string) $this->request->getPost('isin'));
        $firmId = (int) $this->request->getPost('firm_id');
        $ownership = $this->request->getPost('ownership');
        $tab = $this->getActiveTab('shareholders');

        if ($ownership === '' || $ownership === null) {
            return redirect()->back()->with('alert', 'Quota non valida.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(ShareholderModel::class)->updateRow($isin, $firmId, [
            'ownership' => (float) $ownership,
            'id_user' => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Quota aggiornata.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Aggiornamento quota non riuscito.')->with('alert_type', 'danger')->with('tab', $tab);
    }

    public function deleteShareholder($firm_id, $isin)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        model(ShareholderModel::class)->deleteRow(rawurldecode($isin), (int) $firm_id);

        return redirect()->back()->with('alert', 'Azionista rimosso.')->with('tab', 'shareholders');
    }

    //aggiunge una riga consensus analisti
    public function addConsensus()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $isin = trim((string) $this->request->getPost('isin'));
        $firmId = (int) $this->request->getPost('firm_id');
        $ratingId = (int) $this->request->getPost('rating_id');
        $date = trim((string) $this->request->getPost('date'));
        $targetRaw = $this->request->getPost('target_price');
        $targetPrice = ($targetRaw === '' || $targetRaw === null) ? null : (float) $targetRaw;
        $tab = $this->getActiveTab('consensus');

        if ($isin === '' || $firmId < 1 || $ratingId < 1 || $date === '') {
            return redirect()->back()->with('alert', "Società, casa d'analisi, data e rating sono obbligatori.")->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(AnalystConsensusModel::class)->insertRow([
            'isin' => $isin,
            'firm_id' => $firmId,
            'date' => $date,
            'rating_id' => $ratingId,
            'target_price' => $targetPrice,
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Consensus aggiunto.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Impossibile salvare il consensus.')->with('alert_type', 'danger')->with('tab', $tab);
    }

    //aggiorna data, rating e prezzo obiettivo
    public function updateConsensus()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $analysisId = (int) $this->request->getPost('analysis_id');
        $ratingId = (int) $this->request->getPost('rating_id');
        $date = trim((string) $this->request->getPost('date'));
        $targetRaw = $this->request->getPost('target_price');
        $targetPrice = ($targetRaw === '' || $targetRaw === null) ? null : (float) $targetRaw;
        $tab = $this->getActiveTab('consensus');

        if ($analysisId < 1 || $ratingId < 1 || $date === '') {
            return redirect()->back()->with('alert', 'Dati consensus non validi.')->with('alert_type', 'danger')->with('tab', $tab);
        }

        $ok = model(AnalystConsensusModel::class)->updateRow($analysisId, [
            'rating_id' => $ratingId,
            'date' => $date,
            'target_price' => $targetPrice,
            'id_user' => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        return $ok
            ? redirect()->back()->with('alert', 'Consensus aggiornato.')->with('tab', $tab)
            : redirect()->back()->with('alert', 'Aggiornamento consensus non riuscito.')->with('alert_type', 'danger')->with('tab', $tab);
    }

    //elimina un record consensus
    public function deleteConsensus($analysis_id)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        if (model(AnalystConsensusModel::class)->deleteRow((int) $analysis_id)) {
            return redirect()->back()->with('alert', 'Consensus eliminato.')->with('tab', 'consensus');
        } else {
            return redirect()->back()->with('alert', 'Impossibile eliminare il consensus.')->with('alert_type', 'danger')->with('tab', 'consensus');
        }
    }

    /**
     * @return array<string, int|string|null>
     */
    private function buildFinancialPayload(): array
    {
        $typeId = (int) $this->request->getPost('type_id');
        $cur = trim((string) $this->request->getPost('currency_code'));

        //costruisce riga
        $row = [
            'type_id' => $typeId,
            'currency_code' => $cur,
            'id_user' => $this->session->get('user_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ];

        foreach (FinancialDataModel::BIGINT_FIELDS as $field) {
            $v = $this->request->getPost($field);
            $row[$field] = ($v === '' || $v === null) ? null : (int) $v;
        }

        return $row;
    }

    //parser XML bilanci (percorsi XPath come da specifica progetto)
    /*NON FUNZIONANTE */
    private function parseFinancialStatementsFromFile(string $xmlFilePath): array
    {
        $xml = simplexml_load_file($xmlFilePath);
        if ($xml === false) {
            throw new Exception('Errore nel caricamento del file XML.');
        }

        $results = [];

        $getAmount = static function (string $xpathQuery) use ($xml): array {
            $data = [];
            $nodes = $xml->xpath($xpathQuery);
            if ($nodes === false || $nodes === []) {
                return $data;
            }
            foreach ($nodes as $node) {
                $year = (string) $node['year'];
                $data[$year] = (float) $node;
            }

            return $data;
        };

        $revenues = $getAmount('//IncomeStatement/ProductionValue/Revenue/Amount');

        $years = [];
        $yrNodes = $xml->xpath('//IncomeStatement//Revenue/Amount');
        if ($yrNodes !== false) {
            foreach ($yrNodes as $node) {
                $years[] = (string) $node['year'];
            }
        }

        $years = array_unique($years);
        //se l’XPath anni è vuoto, usa gli anni presenti nei ricavi
        if ($years === [] && $revenues !== []) {
            $years = array_keys($revenues);
        }
        rsort($years, SORT_NUMERIC);

        $revenues = $revenues !== [] ? $revenues : $getAmount('//IncomeStatement//Revenue/Amount');
        $netProfit = $getAmount('//IncomeStatement/NetProfitLoss/Amount');
        $cashEquiv = $getAmount('//BalanceSheet/Assets/CurrentAssets/CashAndCashEquivalents/TotalCashAndCashEquivalents/Amount');
        $incomeTaxes = $getAmount('//IncomeStatement/IncomeTaxes/TotalIncomeTaxes/Amount');
        $interests = $getAmount('//IncomeStatement/FinancialIncomeExpenses/InterestAndOtherFinancialExpenses/Amount');
        $amortization = $getAmount('//IncomeStatement/ProductionCosts/AmortizationIntangibleAssets/Amount');
        $depreciation = $getAmount('//IncomeStatement/ProductionCosts/DepreciationPropertyPlantEquipment/Amount');
        $bankLoans = $getAmount('//BalanceSheet/LiabilitiesAndEquity/Liabilities/BankLoans/Total/Amount');
        $bonds = $getAmount('//BalanceSheet/LiabilitiesAndEquity/Liabilities/Bonds/Total/Amount');
        $convertible = $getAmount('//BalanceSheet/LiabilitiesAndEquity/Liabilities/ConvertibleBonds/Total/Amount');
        $otherLenders = $getAmount('//BalanceSheet/LiabilitiesAndEquity/Liabilities/DueToOtherLenders/Total/Amount');

        foreach ($years as $year) {
            $da = ($amortization[$year] ?? 0.0) + ($depreciation[$year] ?? 0.0);

            $totalFinancialDebt = ($bankLoans[$year] ?? 0.0) + ($bonds[$year] ?? 0.0) + ($convertible[$year] ?? 0.0) + ($otherLenders[$year] ?? 0.0);
            $cash = $cashEquiv[$year] ?? 0.0;
            $netDebt = $totalFinancialDebt - $cash;

            //mappatura verso colonne tabella `data` (BIGINT); campi assenti nell’XML restano null
            $results[$year] = [
                'revenues' => $this->xmlFloatToBigInt($revenues[$year] ?? 0.0),
                'amortizations_depretiations' => $this->xmlFloatToBigInt($da),
                'income_taxes' => $this->xmlFloatToBigInt($incomeTaxes[$year] ?? 0.0),
                'interests' => $this->xmlFloatToBigInt($interests[$year] ?? 0.0),
                'net_profit' => $this->xmlFloatToBigInt($netProfit[$year] ?? 0.0),
                'net_debt' => $this->xmlFloatToBigInt($netDebt),
                'share_number' => null,
                'free_cash_flow' => null,
                'capex' => null,
                'dividends' => null,
            ];
        }

        return $results;
    }

    //arrotondamento sicuro da float XML a intero per BIGINT MySQL
    private function xmlFloatToBigInt(float $v): int
    {
        return (int) round($v);
    }
}
