<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioModel extends Model
{
    protected $table = 'portfolios';
    protected $primaryKey = 'portfolio_id';
    protected $allowedFields = [
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

    public function findActiveByUser(int $userId): array
    {
        return $this->where('user_id', $userId)->where('active', 1)->orderBy('name', 'ASC')->findAll();
    }

    //controlla se portfolio è di proprietà dell'utente
    public function findOwnedByUser(int $portfolioId, int $userId): ?array
    {
        return $this->where('portfolio_id', $portfolioId)->where('user_id', $userId)->where('active', 1)->first();
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
    public function adminSearchPaginate(string $searchQuery, int $page, ?string $order = null, ?string $orderType = 'ASC'): array
    {
        $builder = $this->select('portfolios.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.user_id = portfolios.user_id', 'left')
            ->where('portfolios.active', 1);

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $builder->groupStart()
                ->like('portfolios.name', $searchQuery)
                ->orLike('users.email', $searchQuery)
                ->orLike('users.first_name', $searchQuery)
                ->orLike('users.last_name', $searchQuery)
                ->groupEnd();
        }

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
     * @param array<string, float> $priceMap ticker|mic => prezzo
     */
    public function attachMarketMetrics(array $pf, array $priceMap): array
    {
        $open = model(OrderModel::class)->findOpenByPortfolio((int) $pf['portfolio_id']);
        $mv = 0;
        $unreal = 0.0;
        foreach ($open as $o) {
            $k = $o['ticker'] . '|' . $o['mic'];
            $p = $priceMap[$k] ?? null;
            if ($p === null) {
                $p = (float) $o['buyPrice'];
            }
            $mv += (int) $o['quantity'] * (float) $p;
            $unreal += ((float) $p - (float) $o['buyPrice']) * (int) $o['quantity'];
        }

        $liq = (int) $pf['liquidity'];
        $pf['market_value_open'] = (int) round($mv);
        $pf['unrealized_pnl'] = round($unreal, 2);
        $pf['total_value'] = $liq + $pf['market_value_open'];
        $inv = (int) $pf['invested'];
        $pf['unrealized_pct'] = $inv > 0 ? round(($unreal / $inv) * 100, 2) : null;

        return $pf;
    }
}
