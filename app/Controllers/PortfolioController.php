<?php

namespace App\Controllers;

use App\Models\ListingModel;
use App\Models\OrderModel;
use App\Models\PortfolioModel;
use App\Models\PriceModel;

class PortfolioController extends BaseController
{
    private function loginRedirect()
    {
        if (!$this->session->has('logged')) {
            return redirect()->to('/')->with('alert', 'Accedi per continuare.');
        }

        return null;
    }

    public function index()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $uid = (int) $this->session->get('user_id');
        $portfolios = model(PortfolioModel::class)->findActiveByUser($uid);

        $pfModel = model(PortfolioModel::class);
        $enriched = [];
        foreach ($portfolios as $pf) {
            $enriched[] = $pfModel->attachMarketMetrics($pf);
        }

        echo view('templates/header');
        echo view('pages/viewPortfolios', ['portfolios' => $enriched, 'adminSection' => false]);
        echo view('templates/footer');
    }

    //storico ordini utente. se portfolio_id in query string filtra per quel portafoglio,
    //altrimenti mostra tutti. supporta ordinamento e passa i portafogli per il dropdown filtro
    public function orders()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $uid = (int) $this->session->get('user_id');
        $portfolioId = (int) ($this->request->getGet('portfolio_id') ?? 0);

        //ordinamento: default per data apertura DESC
        $sortField = $this->request->getGet('sort') ?? 'orders.date';
        $sortDir = strtoupper($this->request->getGet('dir') ?? 'DESC');
        //whitelist campi consentiti per evitare sql injection
        $allowedSort = ['orders.date', 'orders.order_id'];
        if (!in_array($sortField, $allowedSort)) {
            $sortField = 'orders.date';
        }
        if (!in_array($sortDir, ['ASC', 'DESC'])) {
            $sortDir = 'DESC';
        }

        $orders = model(OrderModel::class)->findHistoryByUser($uid, $portfolioId, $sortField, $sortDir);
        $priceMap = model(PriceModel::class)->getLatestPriceMap();

        foreach ($orders as &$o) {
            $k = $o['ticker'] . '|' . $o['mic'];
            $o['last_price'] = $priceMap[$k] ?? null;
            $o['unrealized'] = null;
            if ((int) $o['status'] === OrderModel::STATUS_OPEN && $o['last_price'] !== null) {
                $o['unrealized'] = round(((float) $o['last_price'] - (float) $o['buyPrice']) * (int) $o['quantity'], 2);
            }
            $o['realized'] = null;
            if ((int) $o['status'] === OrderModel::STATUS_CLOSED && $o['sellPrice'] !== null) {
                $gross = ((float) $o['sellPrice'] - (float) $o['buyPrice']) * (int) $o['quantity'];
                $tax = $gross > 0 ? round($gross * 0.16, 2) : 0;
                $o['realized'] = round($gross - $tax, 2);
            }
        }
        unset($o);

        //portafogli utente per il dropdown di filtro (visibile solo se non filtrato)
        $portfolios = model(PortfolioModel::class)->findActiveByUser($uid);

        echo view('templates/header');
        echo view('pages/viewPortfolioOrders', [
            'orders' => $orders,
            'portfolios' => $portfolios,
            'filterPortfolioId' => $portfolioId,
            'currentSort' => $sortField,
            'currentDir' => $sortDir,
            'adminSection' => false,
        ]);
        echo view('templates/footer');
    }

    public function createPortfolio()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->with('alert', 'Nome portafoglio obbligatorio.')->with('alert_type', 'danger');
        }

        $uid = (int) $this->session->get('user_id');
        model(PortfolioModel::class)->insert([
            'user_id' => $uid,
            'name' => $name,
            'inital_liquidity' => 10000,
            'liquidity' => 10000,
            'invested' => 0,
            'active' => 1,
            'id_user' => $uid,
        ]);

        return redirect()->back()->with('alert', 'Portafoglio creato.')->with('alert_type', 'success');
    }

    public function buy()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $uid = (int) $this->session->get('user_id');
        $portfolioId = (int) $this->request->getPost('portfolio_id');
        $ticker = strtoupper(trim((string) $this->request->getPost('ticker')));
        $mic = strtoupper(trim((string) $this->request->getPost('mic')));
        $qty = (int) $this->request->getPost('quantity');

        if ($portfolioId < 1 || $ticker === '' || $mic === '' || $qty < 1) {
            return redirect()->back()->with('alert', 'Dati ordine non validi.')->with('alert_type', 'danger');
        }

        $pfModel = model(PortfolioModel::class);
        $portfolio = $pfModel->findOwnedByUser($portfolioId, $uid);
        if (!$portfolio) {
            return redirect()->back()->with('alert', 'Portafoglio non valido.')->with('alert_type', 'danger');
        }

        $listing = model(ListingModel::class)->where('ticker', $ticker)->where('mic', $mic)->where('active', 1)->first();
        if (!$listing) {
            return redirect()->back()->with('alert', 'Titolo non disponibile.')->with('alert_type', 'danger');
        }

        $latest = model(PriceModel::class)->getLatestForListing($ticker, $mic);
        if (!$latest || $latest['price'] === null) {
            return redirect()->back()->with('alert', 'Prezzo non disponibile: riprova dopo l\'aggiornamento quotazioni.')->with('alert_type', 'danger');
        }

        $unit = (float) $latest['price'];
        $cost = (int) round($qty * $unit);
        if ($cost < 1) {
            return redirect()->back()->with('alert', 'Importo troppo basso.')->with('alert_type', 'danger');
        }

        if ((int) $portfolio['liquidity'] < $cost) {
            return redirect()->back()->with('alert', 'Liquidità insufficiente.')->with('alert_type', 'danger');
        }

        $db = db_connect();
        $db->transStart();

        $pfModel->update($portfolioId, [
            'liquidity' => (int) $portfolio['liquidity'] - $cost,
            'invested' => (int) $portfolio['invested'] + $cost,
            'id_user' => $uid,
        ]);

        model(OrderModel::class)->insert([
            'ticker' => $ticker,
            'mic' => $mic,
            'quantity' => $qty,
            'buyPrice' => round($unit, 2),
            'sellPrice' => null,
            'date' => date('Y-m-d H:i:s'),
            'portfolio_id' => $portfolioId,
            'status' => OrderModel::STATUS_OPEN,
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Errore durante l\'acquisto.')->with('alert_type', 'danger');
        }

        return redirect()->back()->with('alert', 'Ordine eseguito al prezzo di € ' . number_format($unit, 2, ',', '.'))->with('alert_type', 'success');
    }

    public function close()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $uid = (int) $this->session->get('user_id');
        $orderId = (int) $this->request->getPost('order_id');
        if ($orderId < 1) {
            return redirect()->back()->with('alert', 'Ordine non valido.');
        }

        $orderModel = model(OrderModel::class);
        $order = $orderModel->findOwnedOpen($orderId, $uid);
        if (!$order) {
            return redirect()->back()->with('alert', 'Ordine non trovato o già chiuso.');
        }

        $ticker = $order['ticker'];
        $mic = $order['mic'];
        $qty = (int) $order['quantity'];
        $buy = (float) $order['buyPrice'];

        $latest = model(PriceModel::class)->getLatestForListing($ticker, $mic);
        if (!$latest || $latest['price'] === null) {
            return redirect()->back()->with('alert', 'Prezzo di chiusura non disponibile.');
        }

        $sell = (float) $latest['price'];
        $gross = ($sell - $buy) * $qty;
        $tax = $gross > 0 ? (int) round($gross * 0.16) : 0;
        $cashIn = (int) round($qty * $sell) - $tax;
        $costBasis = (int) round($qty * $buy);

        $portfolio = model(PortfolioModel::class)->findOwnedByUser((int) $order['portfolio_id'], $uid);
        if (!$portfolio) {
            return redirect()->back()->with('alert', 'Portafoglio non trovato.');
        }

        $db = db_connect();
        $db->transStart();

        model(PortfolioModel::class)->update((int) $order['portfolio_id'], [
            'liquidity' => (int) $portfolio['liquidity'] + $cashIn,
            'invested' => max(0, (int) $portfolio['invested'] - $costBasis),
            'id_user' => $uid,
        ]);

        $orderModel->update($orderId, [
            'sellPrice' => round($sell, 2),
            'status' => OrderModel::STATUS_CLOSED,
            'closed_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Errore in chiusura.')->with('alert_type', 'danger');
        }

        $msg = 'Posizione chiusa. P&L netto (dopo tasse su plusvalenza): € ' . number_format($gross - $tax, 2, ',', '.');

        return redirect()->back()->with('alert', $msg)->with('alert_type', 'success');
    }

    public function deletePortfolio()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }


        $portfolioId = (int) $this->request->getGet('portfolio_id');
        if ($portfolioId < 1) {
            return redirect()->back()->with('alert', 'Portafoglio non valido.');
        }
        //controlla se portfolio è di proprietà dell'utente
        $pfModel = model(PortfolioModel::class);
        $portfolio = $pfModel->findOwnedByUser($portfolioId, $this->session->get('user_id'));
        if (empty($portfolio) || !$portfolio) {
            return redirect()->back()->with('alert', 'Portafoglio non trovato.');
        }
        $pfModel->deletePortfolio($portfolioId);
        return redirect()->back()->with('alert', 'Portafoglio eliminato.')->with('alert_type', 'success');
    }

    //ritorna json per ajax
    public function updatePortfolioName()
    {
        if ($r = $this->loginRedirect()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non autenticato']);
        }

        $portfolioId = (int) $this->request->getPost('portfolio_id');
        $name = trim((string) $this->request->getPost('name'));
        if ($portfolioId < 1 || $name === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Dati non validi']);
        }

        $pfModel = model(PortfolioModel::class);
        $portfolio = $pfModel->findOwnedByUser($portfolioId, (int) $this->session->get('user_id'));
        if (empty($portfolio) || !$portfolio) {
            return $this->response->setJSON(['success' => false, 'message' => 'Portafoglio non trovato']);
        }

        $pfModel->updateName($portfolioId, $name);

        return $this->response->setJSON(['success' => true, 'message' => 'Nome aggiornato']);
    }

    //endpoint json per refresh ajax dei portafogli utente con metriche calcolate
    public function refreshPortfolios()
    {
        if (!$this->session->has('logged')) {
            return $this->response->setJSON(['success' => false]);
        }

        $uid = (int) $this->session->get('user_id');
        $portfolios = model(PortfolioModel::class)->findActiveByUser($uid);
        $pfModel = model(PortfolioModel::class);

        $enriched = [];
        foreach ($portfolios as $pf) {
            $enriched[] = $pfModel->attachMarketMetrics($pf);
        }

        return $this->response->setJSON(['success' => true, 'portfolios' => $enriched]);
    }

    //endpoint json per refresh ajax degli ordini utente con prezzi aggiornati
    public function refreshOrders()
    {
        if (!$this->session->has('logged')) {
            return $this->response->setJSON(['success' => false]);
        }

        $uid = (int) $this->session->get('user_id');
        $orders = model(OrderModel::class)->findHistoryByUser($uid);
        $priceMap = model(PriceModel::class)->getLatestPriceMap();

        foreach ($orders as &$o) {
            $k = $o['ticker'] . '|' . $o['mic'];
            $o['last_price'] = $priceMap[$k] ?? null;
            $o['unrealized'] = null;
            if ((int) $o['status'] === OrderModel::STATUS_OPEN && $o['last_price'] !== null) {
                $o['unrealized'] = round(((float) $o['last_price'] - (float) $o['buyPrice']) * (int) $o['quantity'], 2);
            }
            $o['realized'] = null;
            if ((int) $o['status'] === OrderModel::STATUS_CLOSED && $o['sellPrice'] !== null) {
                $gross = ((float) $o['sellPrice'] - (float) $o['buyPrice']) * (int) $o['quantity'];
                $tax = $gross > 0 ? round($gross * 0.16, 2) : 0;
                $o['realized'] = round($gross - $tax, 2);
            }
        }
        unset($o);

        return $this->response->setJSON(['success' => true, 'orders' => $orders]);
    }
}
