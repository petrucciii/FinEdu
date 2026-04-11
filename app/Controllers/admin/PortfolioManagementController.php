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


        //ordina campi calcolati (metriche)
        if (str_starts_with($orderField, 'calculated_')) {

            $allPortfolios = $pfModel->findActive();
            //filtri ricerca
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
                $enriched[] = $pfModel->attachMarketMetrics($pf);//portafoglio con metriche (p&l, controvalore ecc)
            }

            //nomi colonne convertiti in metriche
            $map = [
                'calculated_mv' => 'market_value_open',
                'calculated_total' => 'total_value',
                'calculated_pnl' => 'unrealized_pnl'
            ];
            $sortKey = $map[$orderField] ?? 'portfolio_id';

            //ordinamento in php
            usort($enriched, function ($a, $b) use ($sortKey, $orderType) {
                $valA = $a[$sortKey] ?? 0;
                $valB = $b[$sortKey] ?? 0;
                if ($valA == $valB)
                    return 0;
                if ($orderType === 'ASC') {
                    return $valA < $valB ? -1 : 1;
                } else {
                    return $valA > $valB ? -1 : 1;
                }
            });

            //pagination manuale
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
            //ordinamento altri campi
            $result = $pfModel->adminSearchPaginate((string) $query, $page, $orderField, $orderType);
            $rows = [];
            foreach ($result['portfolios'] as $pf) {
                $rows[] = $pfModel->attachMarketMetrics($pf);
            }
            //ritorno dati impaginati
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
