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
        $result = model(PortfolioModel::class)->adminSearchPaginate((string) $query, $page);
        $priceMap = model(PriceModel::class)->getLatestPriceMap();
        $pfModel = model(PortfolioModel::class);

        $rows = [];
        foreach ($result['portfolios'] as $pf) {
            //$rows[] = $pfModel->attachMarketMetrics($pf, $priceMap);

        }

        $pager = $result['pager'];

        return $this->response->setJSON([
            'portfolios' => $result['portfolios'],//$rows,
            'pagination' => [
                'currentPage' => $pager->getCurrentPage(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'pageCount' => $pager->getPageCount(),
            ],
        ]);
    }
}
