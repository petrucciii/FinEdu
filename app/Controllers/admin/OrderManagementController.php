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

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $orderModel = model(OrderModel::class);
        $users = model(UserModel::class)->fread(['users.role_id' => 2]);
        $ex = model(ExchangeModel::class)->fread();
        $data = [
            'users' => is_array($users) ? $users : [],
            'portfolios' => model(PortfolioModel::class)->where('active', 1)->orderBy('name', 'ASC')->findAll(),
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

    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $userId = $this->request->getGet('user_id');
        $portfolioId = $this->request->getGet('portfolio_id');
        $ticker = $this->request->getGet('ticker');
        $mic = $this->request->getGet('mic');
        $status = $this->request->getGet('status');
        $dateFrom = $this->request->getGet('date_from');
        $pnlMin = $this->request->getGet('pnl_min');
        $pnlMax = $this->request->getGet('pnl_max');

        $result = model(OrderModel::class)->searchAdminOrders(
            (string) $query,
            $userId,
            $portfolioId,
            $ticker,
            $mic,
            $status,
            $dateFrom,
            $pnlMin,
            $pnlMax,
            $page
        );

        return $this->response->setJSON([
            'orders' => $result['orders'],
            'pagination' => $result['pager'],
        ]);
    }
}
