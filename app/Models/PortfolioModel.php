<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\PriceModel;

class PortfolioModel extends Model
{
    protected $table = 'portfolios';
    protected $primaryKey = 'portfolio_id';
    protected $allowedFields = [
        'portfolio_id',
        'user_id',
        'inital_liquidity',
        'liquidity',
        'invested',
        'name',
        'active',
        'id_user',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';


    //trova portafoglii di ogni utente
    public function findActiveByUser(int $userId)
    {
        try {
            return $this->where('user_id', $userId)->where('active', 1)->orderBy('name', 'ASC')->findAll();
        } catch (\Throwable $th) {
            return false;
        }

    }

    //controlla se portfolio è di proprietà dell'utente
    public function findOwnedByUser(int $portfolioId, int $userId)
    {
        try {
            return $this->where('portfolio_id', $portfolioId)->where('user_id', $userId)->where('active', 1)->first();
        } catch (\Throwable $th) {
            return false;
        }

    }

    //soft delete
    public function deletePortfolio(int $portfolioId): bool
    {
        try {
            return $this->update($portfolioId, ['active' => 0]);
        } catch (\Exception $e) {
            return false;
        }
    }

    //aggiorna il nome del portafoglio
    public function updateName(int $portfolioId, string $name): bool
    {
        try {
            return $this->update($portfolioId, ['name' => $name]);
        } catch (\Exception $e) {
            return false;
        }
    }

    //ricerca e paginazione per admin
    public function adminSearchPaginate(string $searchQuery, int $page, ?string $order = null, ?string $orderType = 'ASC'): array
    {
        //seleziona portfogli e dati utenti con left join così da mostrare anche utenti senza portafogli
        $builder = $this->select('portfolios.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.user_id = portfolios.user_id', 'left')
            ->where('portfolios.active', 1);

        //filtro ricerca 
        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $builder->groupStart()
                ->like('portfolios.name', $searchQuery)
                ->orLike('users.email', $searchQuery)
                ->orLike('users.first_name', $searchQuery)
                ->orLike('users.last_name', $searchQuery)
                ->groupEnd();
        }

        //ordinamento
        if ($order) {
            $builder->orderBy($order, $orderType);
        } else {
            $builder->orderBy('portfolios.portfolio_id', 'DESC');
        }

        return [
            'portfolios' => $builder->paginate(12, 'default', $page),
            'pager' => $this->pager,
        ];
    }

    /**
     * calcola metriche (controvalore, liquidità, profitto/perdita non realizzati) per un portafoglio
     */
    public function attachMarketMetrics(array $portfolioId): array
    {
        $open = model(OrderModel::class)->findOpenByPortfolio((int) $portfolioId['portfolio_id']);//ordini aperti del portafoglio

        $mv = 0;
        $unreal = 0.0;

        foreach ($open as $o) {
            $currentPrice = model(PriceModel::class)->getLatestForListing($o['ticker'], $o['mic']) ?? null;

            //se ultimo prezzo non disoponibile usa buyprice
            if ($currentPrice === null) {
                $currentPrice = (float) $o['buyPrice'];
            }

            $mv += (int) $o['quantity'] * (float) $currentPrice;//controvalore attuale

            $unreal += ((float) $currentPrice - (float) $o['buyPrice']) * (int) $o['quantity'];//profitto/perdita non realizzato per ordine
        }

        $liq = (int) $portfolioId['liquidity'];//cash disponibile

        $portfolioId['market_value_open'] = (int) round($mv);//aggiunge campo market value a portafoglio
        $portfolioId['unrealized_pnl'] = round($unreal, 2);//aggiunge unrealized profit and loss
        //calcola il valore totale del portafoglio: liquidità disponibile + valore attuale dei titoli posseduti
        $portfolioId['total_value'] = $liq + $portfolioId['market_value_open'];
        $inv = (int) $portfolioId['invested'];//capitale investito

        //calcola la performance percentuale: se non ci sono investimenti evita la divisione per zero restituendo null
        $portfolioId['unrealized_pct'] = $inv > 0 ? round(($unreal / $inv) * 100, 2) : null;

        return $portfolioId;//ritorna array con nuovi campi calcolati
    }
}
