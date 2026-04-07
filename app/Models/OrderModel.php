<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $allowedFields = [
        'ticker',
        'mic',
        'quantity',
        'buyPrice',
        'sellPrice',
        'closed_at',
        'date',
        'portfolio_id',
        'status',
    ];

    public const STATUS_OPEN = 1;
    public const STATUS_CLOSED = 0;

    public function findOpenByPortfolio(int $portfolioId): array
    {
        return $this->select('orders.*, listings.isin, companies.name AS company_name')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('orders.portfolio_id', $portfolioId)
            ->where('orders.status', self::STATUS_OPEN)
            ->orderBy('orders.order_id', 'DESC')
            ->findAll();
    }

    //storico ordini utente con join exchange per short_name.
    //se portfolioId > 0 filtra per singolo portafoglio, altrimenti mostra tutti.
    //orderBy permette di ordinare per data (default: order_id DESC)
    public function findHistoryByUser(int $userId, int $portfolioId = 0, string $orderBy = 'orders.order_id', string $orderDir = 'DESC'): array
    {
        $builder = $this->select('orders.*, portfolios.name AS portfolio_name, portfolios.portfolio_id,
                listings.isin, companies.name AS company_name, exchanges.short_name AS exchange_short')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->join('exchanges', 'exchanges.mic = orders.mic', 'left')
            ->where('portfolios.user_id', $userId)
            ->where('portfolios.active', 1);

        //filtro per singolo portafoglio se specificato
        if ($portfolioId > 0) {
            $builder->where('orders.portfolio_id', $portfolioId);
        }

        return $builder->orderBy($orderBy, $orderDir)->findAll();
    }

    public function findOwnedOpen(int $orderId, int $userId): ?array
    {
        $row = $this->select('orders.*')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->where('orders.order_id', $orderId)
            ->where('portfolios.user_id', $userId)
            ->where('orders.status', self::STATUS_OPEN)
            ->first();

        return $row ?: null;
    }

    /**
     * Ricerca admin con filtri (join complessi nel Model).
     *
     * @return array{orders: array, pager: array}
     */
    public function searchAdminOrders(
        string $q,
        $userId,
        $portfolioId,
        $ticker,
        $mic,
        $status,
        $dateFrom,
        $pnlMin,
        $pnlMax,
        int $page
    ): array {
        // SQL per calcolare il PnL realizzato
        $pnlSql = '(CASE WHEN orders.status = 0 AND orders.sellPrice IS NOT NULL THEN ROUND((orders.sellPrice - orders.buyPrice) * orders.quantity - IF((orders.sellPrice - orders.buyPrice) * orders.quantity > 0, 0.16 * (orders.sellPrice - orders.buyPrice) * orders.quantity, 0), 2) ELSE NULL END)';

        // Query per ottenere gli ordini con filtri
        $builder = $this->db->table('orders')
            ->select('orders.*, portfolios.name AS portfolio_name, portfolios.user_id,
                users.first_name, users.last_name, users.email,
                listings.isin, companies.name AS company_name, ' . $pnlSql . ' AS realized_pnl', false)
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left');

        $q = trim($q);
        if ($q !== '') {
            $builder->groupStart()
                ->like('orders.ticker', $q)
                ->orLike('companies.name', $q)
                ->orLike('users.email', $q)
                ->orLike('users.first_name', $q)
                ->orLike('users.last_name', $q)
                ->orLike('portfolios.name', $q)
                ->groupEnd();
        }

        if ($userId !== '' && $userId !== null && $userId !== 'all' && is_numeric($userId)) {
            $builder->where('portfolios.user_id', (int) $userId);
        }

        if ($portfolioId !== '' && $portfolioId !== null && $portfolioId !== 'all' && is_numeric($portfolioId)) {
            $builder->where('orders.portfolio_id', (int) $portfolioId);
        }

        if ($ticker !== '' && $ticker !== null) {
            $builder->like('orders.ticker', trim((string) $ticker));
        }

        if ($mic !== '' && $mic !== null) {
            $builder->where('orders.mic', trim((string) $mic));
        }

        if ($status !== '' && $status !== null && $status !== 'all' && is_numeric($status)) {
            $builder->where('orders.status', (int) $status);
        }

        if ($dateFrom !== '' && $dateFrom !== null) {
            $builder->where('DATE(orders.date) >=', $dateFrom);
        }

        if ($pnlMin !== '' && $pnlMin !== null && is_numeric($pnlMin)) {
            $builder->where($pnlSql . ' >= ' . (float) $pnlMin, null, false);
            $builder->where('orders.status', self::STATUS_CLOSED);
            $builder->where('orders.sellPrice IS NOT NULL', null, false);
        }

        if ($pnlMax !== '' && $pnlMax !== null && is_numeric($pnlMax)) {
            $builder->where($pnlSql . ' <= ' . (float) $pnlMax, null, false);
            $builder->where('orders.status', self::STATUS_CLOSED);
            $builder->where('orders.sellPrice IS NOT NULL', null, false);
        }

        $builder->orderBy('orders.order_id', 'DESC');

        $countSql = 'SELECT COUNT(*) AS c FROM (' . $builder->getCompiledSelect(false) . ') _cnt';
        $total = (int) ($this->db->query($countSql)->getRow('c') ?? 0);

        $perPage = 15;
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        foreach ($rows as &$r) {
            if (!array_key_exists('realized_pnl', $r) || $r['realized_pnl'] === '') {
                $r['realized_pnl'] = $this->computeRealizedPnlRow($r);
            }
        }
        unset($r);

        $pageCount = max(1, (int) ceil($total / $perPage));

        return [
            'orders' => $rows,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pageCount' => $pageCount,
            ],
        ];
    }

    private function computeRealizedPnlRow(array $r): ?float
    {
        if ((int) $r['status'] !== self::STATUS_CLOSED || $r['sellPrice'] === null) {
            return null;
        }
        $qty = (int) $r['quantity'];
        $buy = (float) $r['buyPrice'];
        $sell = (float) $r['sellPrice'];
        $gross = ($sell - $buy) * $qty;
        $tax = $gross > 0 ? round($gross * 0.26, 2) : 0.0; // 26% capital gain 

        return round($gross - $tax, 2);
    }

    public function reportTopCompaniesByOpenVolume(int $limit = 8): array
    {
        return $this->db->table('orders')
            ->select('listings.isin, companies.name, SUM(orders.quantity) AS total_qty, COUNT(*) AS num_orders')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('orders.status', self::STATUS_OPEN)
            ->groupBy(['listings.isin', 'companies.name'])
            ->orderBy('total_qty', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function reportTopUsersByRealizedPnl(int $limit = 8): array
    {
        // Query per ottenere gli ordini chiusi
        $rows = $this->db->table('orders')
            ->select('portfolios.user_id, users.first_name, users.last_name,
                orders.quantity, orders.buyPrice, orders.sellPrice, orders.status')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->where('orders.status', self::STATUS_CLOSED)
            ->where('orders.sellPrice IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        $byUser = [];
        foreach ($rows as $r) {
            $uid = (int) $r['user_id'];
            $qty = (int) $r['quantity'];
            $gross = ((float) $r['sellPrice'] - (float) $r['buyPrice']) * $qty;
            $tax = $gross > 0 ? $gross * 0.16 : 0;
            $net = $gross - $tax;
            if (!isset($byUser[$uid])) {
                $byUser[$uid] = [
                    'user_id' => $uid,
                    'first_name' => $r['first_name'],
                    'last_name' => $r['last_name'],
                    'total_pnl' => 0.0,
                    'trades' => 0,
                ];
            }
            $byUser[$uid]['total_pnl'] += $net;
            $byUser[$uid]['trades']++;
        }

        usort($byUser, static function ($a, $b) {
            return $b['total_pnl'] <=> $a['total_pnl'];
        });
        $byUser = array_slice($byUser, 0, $limit);
        foreach ($byUser as &$u) {
            $u['total_pnl'] = round($u['total_pnl'], 2);
        }
        unset($u);

        return $byUser;
    }

    public function reportBestClosedTrades(int $limit = 10): array
    {
        $rows = $this->select('orders.*, portfolios.user_id, users.first_name, users.last_name, companies.name AS company_name')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('orders.status', self::STATUS_CLOSED)
            ->where('orders.sellPrice IS NOT NULL', null, false)
            ->orderBy('orders.order_id', 'DESC')
            ->findAll();

        foreach ($rows as &$r) {
            $r['trade_pnl'] = $this->computeRealizedPnlRow($r);
        }
        unset($r);

        usort($rows, static function ($a, $b) {
            return ($b['trade_pnl'] ?? 0) <=> ($a['trade_pnl'] ?? 0);
        });

        return array_slice($rows, 0, $limit);
    }

    public function countTodayOrders(): int
    {
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');

        return (int) $this->where('orders.date >=', $start)->where('orders.date <=', $end)->countAllResults();
    }

    public function findRecentForDashboard(int $limit = 12): array
    {
        return $this->select('orders.order_id, orders.ticker, orders.quantity, orders.buyPrice, orders.sellPrice, orders.status, orders.date,
            portfolios.name AS portfolio_name, users.first_name, users.last_name, users.email, companies.name AS company_name')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->orderBy('orders.order_id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function findRecentNewsForDashboard(int $limit = 5): array
    {
        return $this->db->table('news')
            ->select('news.headline, news.date')
            ->where('news.active', 1)
            ->orderBy('news.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
