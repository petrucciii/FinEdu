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

    //costanti per stato dell'ordine
    public const STATUS_OPEN = 1;
    public const STATUS_CLOSED = 0;

    //ordini aperti di un portafoglio con joi per dati necessari alle views
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

    //ricerca ordine di un utente (verifica che ordine sia di prorprità dell'utente)
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

    //ricerca e impagina
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
        /*
         * Calcolo del P&L realizzato direttamente in SQL.
         *
         * Il CASE restituisce un valore solo per ordini chiusi con sellPrice valorizzato.
         * La tassazione al 26% viene sottratta solo se il risultato lordo è positivo.
         * Tenere la formula in SQL permette anche di usarla nei filtri pnl_min/pnl_max.
         */
        $pnlSql = '(CASE 
                        WHEN orders.status = 0 AND 
                        orders.sellPrice IS NOT NULL 
                    THEN ROUND((orders.sellPrice - orders.buyPrice) * orders.quantity - IF((orders.sellPrice - orders.buyPrice) * orders.quantity > 0, 0.26 * (orders.sellPrice - orders.buyPrice) * orders.quantity, 0), 2) 
                    ELSE 
                        NULL 
                    END
                    )';//calcoal controvalore dell ordine, se chiuso, e quindi sellPrice valorizzato, sottrae il il buyPirce moltiplicato per quantita e toglie gain tax di 26% se positivo

        //query principale: unisce ordine, portafoglio, utente, listing e società per mostrare una riga completa in admin
        $builder = $this->db->table('orders')
            ->select('orders.*, portfolios.name AS portfolio_name, portfolios.user_id,
                users.first_name, users.last_name, users.email,
                listings.isin, companies.name AS company_name, ' . $pnlSql . ' AS realized_pnl', false)
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin');

        /*
         * Ricerca a token nello stesso input: ogni parola può combaciare con ticker,
         * società, email, nome/cognome utente o nome portafoglio.
         */
        $tokens = preg_split('/\s+/', trim($q), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $builder->groupStart()
                ->like('orders.ticker', $token)
                ->orLike('companies.name', $token)
                ->orLike('users.email', $token)
                ->orLike('users.first_name', $token)
                ->orLike('users.last_name', $token)
                ->orLike('portfolios.name', $token)
                ->groupEnd();
        }

        //filtri per utente
        if ($userId !== '' && $userId !== null && $userId !== 'all' && is_numeric($userId)) {
            $builder->where('portfolios.user_id', (int) $userId);
        }

        //filtri per portafoglio
        if ($portfolioId !== '' && $portfolioId !== null && $portfolioId !== 'all' && is_numeric($portfolioId)) {
            $builder->where('orders.portfolio_id', (int) $portfolioId);
        }

        //per ticker
        if ($ticker !== '' && $ticker !== null) {
            $builder->like('orders.ticker', trim((string) $ticker));
        }

        //per borsa
        if ($mic !== '' && $mic !== null) {
            $builder->where('orders.mic', trim((string) $mic));
        }

        //filtro per stato ordine
        if ($status !== '' && $status !== null && $status !== 'all' && is_numeric($status)) {
            $builder->where('orders.status', (int) $status);
        }

        //per data
        if ($dateFrom !== '' && $dateFrom !== null) {
            $builder->where('DATE(orders.date) >=', $dateFrom);
        }

        //filtro profitto minimo: forza ordini chiusi perché solo loro hanno pnl realizzato
        if ($pnlMin !== '' && $pnlMin !== null && is_numeric($pnlMin)) {
            $builder->where($pnlSql . ' >= ' . (float) $pnlMin, null, false);
            $builder->where('orders.status', self::STATUS_CLOSED);
            $builder->where('orders.sellPrice IS NOT NULL', null, false);
        }

        //filtro per profitto massimo
        if ($pnlMax !== '' && $pnlMax !== null && is_numeric($pnlMax)) {
            $builder->where($pnlSql . ' <= ' . (float) $pnlMax, null, false);
            $builder->where('orders.status', self::STATUS_CLOSED);
            $builder->where('orders.sellPrice IS NOT NULL', null, false);
        }

        $builder->orderBy('orders.order_id', 'DESC');

        //impaginazione manuale: necessaria perché il paginate di codeigniter fatica con query complesse.
        //viene creata una subquery per contare il totale dei record filtrati.
        $countSql = 'SELECT COUNT(*) AS c FROM (' . $builder->getCompiledSelect(false) . ') _cnt';
        $total = (int) ($this->db->query($countSql)->getRow('c') ?? 0);

        $perPage = 15;
        $offset = max(0, ($page - 1) * $perPage);
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        //se il calcolo sql non è presente, lo calcola via php per ogni riga.
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

    //calcola il profitto netto di una singola riga ordine.
    private function computeRealizedPnlRow(array $r): ?float
    {
        if ((int) $r['status'] !== self::STATUS_CLOSED || $r['sellPrice'] === null) {
            return null;
        }
        $qty = (int) $r['quantity'];
        $buy = (float) $r['buyPrice'];
        $sell = (float) $r['sellPrice'];
        $gross = ($sell - $buy) * $qty;
        $tax = $gross > 0 ? round($gross * 0.26, 2) : 0.0; //26% capital gain

        return round($gross - $tax, 2);
    }

    //statistica admin: mostra quali aziende hanno più volumi (quantità titoli) negli ordini ancora aperti.
    public function reportTopCompaniesByOpenVolume(int $limit = 8): array
    {
        return $this->select('listings.isin, companies.name, SUM(orders.quantity) AS total_qty, COUNT(*) AS num_orders')//seleziona somma quantità e numero ordini raggruppando per azienda
            ->join('listings', 'listings.ticker = orders.ticker AND listings.mic = orders.mic')
            ->join('companies', 'companies.isin = listings.isin', 'left')
            ->where('orders.status', self::STATUS_OPEN)
            ->groupBy(['listings.isin', 'companies.name'])
            ->orderBy('total_qty', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    //statistica admin: classifica degli utenti che hanno guadagnato di più con posizioni chiuse.
    public function reportTopUsersByRealizedPnl(int $limit = 8): array
    {
        $rows = $this ->select('portfolios.user_id, users.first_name, users.last_name,
                orders.quantity, orders.buyPrice, orders.sellPrice, orders.status')
            ->join('portfolios', 'portfolios.portfolio_id = orders.portfolio_id')
            ->join('users', 'users.user_id = portfolios.user_id')
            ->where('orders.status', self::STATUS_CLOSED)
            ->where('orders.sellPrice IS NOT NULL', null, false)
            ->findAll();

        $byUser = [];
        foreach ($rows as $r) {
            $uid = (int) $r['user_id'];
            $qty = (int) $r['quantity'];
            $gross = ((float) $r['sellPrice'] - (float) $r['buyPrice']) * $qty;//calcolo profitto lordo
            $tax = $gross > 0 ? $gross * 0.26 : 0;
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
            $byUser[$uid]['total_pnl'] += $net;//
            $byUser[$uid]['trades']++;//trade di un utente
        }

        //ordina l'array degli utenti dal profitto più alto al più basso
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

    //statistica admin: mostra i migliori trade singoli mai effettuati nel sistema.
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

        //ordinamento manuale basato sul pnl calcolato
        usort($rows, static function ($a, $b) {
            return ($b['trade_pnl'] ?? 0) <=> ($a['trade_pnl'] ?? 0);
        });

        return array_slice($rows, 0, $limit);
    }

    //conta quanti ordini sono stati inseriti oggi. usato per i widget della dashboard.
    public function countTodayOrders(): int
    {
        $start = date('Y-m-d 00:00:00');//oggi a mezzanotte
        $end = date('Y-m-d 23:59:59');//stasera 23.59

        return (int) $this->where('orders.date >=', $start)->where('orders.date <=', $end)->countAllResults();
    }

    //recupera gli ultimi ordini a prescindere dall'utente. serve alla dashboard amministratore.
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

}
