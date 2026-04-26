<?php

namespace App\Controllers;

use App\Models\ExchangeModel;
use App\Models\ListingModel;
use App\Models\PortfolioModel;

class ListingController extends BaseController
{
    private function loginRedirect()
    {
        if (!$this->session->has('logged')) {
            return redirect()->to('/')->with('alert', 'Accedi per continuare.');
        }

        return null;
    }

    //pagina listings con tabella, filtri e modal ordine
    public function index()
    {
        if ($r = $this->loginRedirect()) {
            return $r;
        }

        $uid = (int) $this->session->get('user_id');
        $exchanges = model(ExchangeModel::class)->fread();
        $userPortfolios = model(PortfolioModel::class)->findActiveByUser($uid);

        echo view('templates/header');
        echo view('pages/viewListings', [
            'exchanges' => $exchanges ?: [],
            'userPortfolios' => $userPortfolios,
            'adminSection' => false,
        ]);
        echo view('templates/footer');
    }

    /*
     * Endpoint JSON per la ricerca AJAX dei listing.
     * La view chiama questo metodo ad ogni ricerca/filtro e riceve righe + paginazione
     * nello stesso formato usato dalle altre tabelle dinamiche.
     */
    public function search($query = '')
    {
        if (!$this->session->has('logged')) {
            return $this->response->setJSON(['success' => false]);
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $mic = trim((string) ($this->request->getGet('mic') ?? ''));

        $result = model(ListingModel::class)->searchPaginate((string) $query, $page, $mic);
        $pager = $result['pager'];

        return $this->response->setJSON([
            'listings' => $result['listings'],
            'pagination' => [
                'currentPage' => $pager->getCurrentPage(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'pageCount' => $pager->getPageCount(),
            ],
        ]);
    }
}
