<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExchangeModel;
use App\Models\OrderModel;
use App\Models\PortfolioModel;
use App\Models\UserModel;

class OrderManagementController extends BaseController
{
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    //load viewCompanyList con flag adminSection= true per mostare view admin
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to(base_url('/'));
        }

        $orderModel = model(OrderModel::class);
        $users = model(UserModel::class)->fread(['users.role_id' => 2]);
        $ex = model(ExchangeModel::class)->fread();
        $data = [
            'users' => is_array($users) ? $users : [],
            'portfolios' => model(PortfolioModel::class)->findActiveOrdered(),
            'exchanges' => is_array($ex) ? $ex : [],
            'topCompanies' => $orderModel->reportTopCompaniesByOpenVolume(8),
            'topUsers' => $orderModel->reportTopUsersByRealizedPnl(8),
            'bestTrades' => $orderModel->reportBestClosedTrades(10),
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewOrderManagement', $data);
        echo view('templates/footer');
    }

    /*
     * Endpoint AJAX della gestione ordini.
     * Tutti i filtri arrivano da query string perché la tabella si aggiorna senza reload;
     * il model centralizza join, calcolo P&L e paginazione manuale.
     */
    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            return redirect()->to(base_url('/'));
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $userId = $this->request->getGet('user_id');
        $portfolioId = $this->request->getGet('portfolio_id');
        $ticker = $this->request->getGet('ticker');
        $mic = $this->request->getGet('mic');
        $status = $this->request->getGet('status');
        $dateFrom = $this->request->getGet('date_start') ?? $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_end');
        $pnlMin = $this->request->getGet('pnl_min');
        $pnlMax = $this->request->getGet('pnl_max');
        $sort = (string) ($this->request->getGet('sort') ?? 'order_id');
        $dir = (string) ($this->request->getGet('dir') ?? 'DESC');

        $dateError = $this->validateDateRange($dateFrom, $dateTo);
        if ($dateError !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => $dateError,
                'orders' => [],
                'pagination' => [
                    'currentPage' => 1,
                    'perPage' => 15,
                    'total' => 0,
                    'pageCount' => 1,
                ],
            ]);
        }

        $result = model(OrderModel::class)->searchAdminOrders(
            (string) $query,
            $userId,
            $portfolioId,
            $ticker,
            $mic,
            $status,
            $dateFrom,
            $dateTo,
            $pnlMin,
            $pnlMax,
            $page,
            $sort,
            $dir
        );

        return $this->response->setJSON([
            'orders' => $result['orders'],
            'pagination' => $result['pager'],
        ]);
    }

    private function validateDateRange($dateFrom, $dateTo): ?string
    {
        //controlli server side allineati ai controlli client sulle date
        $from = $this->parseDate($dateFrom);
        $to = $this->parseDate($dateTo);
        $today = new \DateTimeImmutable('today');

        if ($dateFrom && !$from) {
            return 'Data inizio ordine non valida.';
        }
        if ($dateTo && !$to) {
            return 'Data fine ordine non valida.';
        }
        if ($from && $from > $today) {
            return 'La data inizio ordine non puo essere futura.';
        }
        if ($to && $to > $today) {
            return 'La data fine ordine non puo essere futura.';
        }
        if ($from && $to && $from > $to) {
            return 'La data inizio ordine non puo essere successiva alla data fine.';
        }

        return null;
    }

    private function parseDate($value): ?\DateTimeImmutable
    {
        //accetta solo il formato prodotto dagli input type=date
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }
}
