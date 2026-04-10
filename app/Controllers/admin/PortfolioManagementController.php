<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PortfolioModel;
use App\Models\PriceModel;

class PortfolioManagementController extends BaseController
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

        echo view('templates/header');
        echo view('pages/admins/viewPortfolioManagement', ['adminSection' => true]);
        echo view('templates/footer');
    }

    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $orderField = (string) $this->request->getGet('order');
        $orderType = strtoupper((string) ($this->request->getGet('order_type') ?? 'ASC'));
        $perPage = 12;

        $pfModel = model(PortfolioModel::class);
        $priceModel = model(PriceModel::class);

        // Check if sorting is by calculated field
        if (str_starts_with($orderField, 'calculated_')) {
            // Calculated fields sorting (requires fetching all matches, attaching metrics, then sorting)
            $allPortfolios = $pfModel->select('portfolios.*, users.first_name, users.last_name, users.email')
                ->join('users', 'users.user_id = portfolios.user_id', 'left')
                ->where('portfolios.active', 1);

            $query = trim((string) $query);
            if ($query !== '') {
                $allPortfolios->groupStart()
                    ->like('portfolios.name', $query)
                    ->orLike('users.email', $query)
                    ->orLike('users.first_name', $query)
                    ->orLike('users.last_name', $query)
                    ->groupEnd();
            }

            $results = $allPortfolios->findAll();
            $enriched = [];
            foreach ($results as $pf) {
                $enriched[] = $pfModel->attachMarketMetrics($pf);
            }

            // Map field names
            $map = [
                'calculated_mv' => 'market_value_open',
                'calculated_total' => 'total_value',
                'calculated_pnl' => 'unrealized_pnl'
            ];
            $sortKey = $map[$orderField] ?? 'portfolio_id';

            // Sort in PHP
            usort($enriched, function ($a, $b) use ($sortKey, $orderType) {
                $valA = $a[$sortKey] ?? 0;
                $valB = $b[$sortKey] ?? 0;
                if ($valA == $valB) return 0;
                if ($orderType === 'ASC') {
                    return $valA < $valB ? -1 : 1;
                } else {
                    return $valA > $valB ? -1 : 1;
                }
            });

            // Manual pagination
            $total = count($enriched);
            $pageCount = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $rows = array_slice($enriched, $offset, $perPage);

            return $this->response->setJSON([
                'portfolios' => $rows,
                'pagination' => [
                    'currentPage' => $page,
                    'perPage' => $perPage,
                    'total' => $total,
                    'pageCount' => $pageCount,
                ],
            ]);
        } else {
            // Standard DB sorting
            $result = $pfModel->adminSearchPaginate((string) $query, $page, $orderField, $orderType);
            $rows = [];
            foreach ($result['portfolios'] as $pf) {
                $rows[] = $pfModel->attachMarketMetrics($pf);
            }

            $pager = $result['pager'];
            return $this->response->setJSON([
                'portfolios' => $rows,
                'pagination' => [
                    'currentPage' => $pager->getCurrentPage(),
                    'perPage' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'pageCount' => $pager->getPageCount(),
                ],
            ]);
        }
    }
}
