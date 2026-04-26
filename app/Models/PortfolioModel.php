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

    /*
     * Restituisce un builder con i portafogli attivi e i dati essenziali dell'utente.
     *
     * Il metodo ritorna il builder, non subito l'array, per permettere al controller di
     * aggiungere filtri e ordinamenti particolari prima di chiamare findAll(). La LEFT JOIN
     * mantiene visibile il portafoglio anche se il dato utente collegato fosse incompleto.
     * Il parametro userId viene usato dal bottone "Visualizza Portafogli" nel modal utenti.
     */
    public function findActive(?int $userId = null)
    {
        try {
            $builder = $this->select('portfolios.*, users.first_name, users.last_name, users.email')
                ->join('users', 'users.user_id = portfolios.user_id', 'left')
                ->where('portfolios.active', 1);

            if ($userId !== null && $userId > 0) {
                $builder->where('portfolios.user_id', $userId);
            }

            return $builder;
        } catch (\Throwable $th) {
            return false;
        }
    }

    //trova portafoglii di ogni utente
    public function findActiveByUser(int $userId)
    {
        try {
            return $this->where('user_id', $userId)->where('active', 1)->orderBy('name', 'ASC')->findAll();
        } catch (\Throwable $th) {
            return false;
        }

    }

    public function findActiveOrdered(): array
    {
        //elenco portafogli attivi per filtri admin.
        return $this->where('active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
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

    /*
     * Ricerca e paginazione per la view admin portafogli.
     *
     * La ricerca usa lo stesso input per nome portafoglio, email, nome e cognome utente.
     * Il filtro userId e' opzionale: quando arriva dal modal utenti limita la tabella ai
     * soli portafogli dell'utente selezionato, mantenendo pero' la stessa view admin.
     */
    public function adminSearchPaginate(string $searchQuery, int $page, ?string $order = null, ?string $orderType = 'ASC', ?int $userId = null): array
    {
        //seleziona portfogli e dati utenti con left join così da mostrare anche utenti senza portafogli
        $builder = $this->select('portfolios.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.user_id = portfolios.user_id', 'left')
            ->where('portfolios.active', 1);

        if ($userId !== null && $userId > 0) {
            $builder->where('portfolios.user_id', $userId);
        }

        /*
         * Ricerca a token nello stesso input. Ogni parola digitata viene cercata su tutte
         * le colonne testuali utili, quindi "rossi portfolio" puo' filtrare insieme per
         * cognome utente e nome portafoglio.
         */
        $tokens = preg_split('/\s+/', trim($searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $builder->groupStart()
                ->like('portfolios.name', $token)
                ->orLike('users.email', $token)
                ->orLike('users.first_name', $token)
                ->orLike('users.last_name', $token)
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

    /*
     * Calcola metriche derivate per un portafoglio.
     *
     * Le metriche non sono salvate direttamente nella tabella portfolios perche' dipendono
     * dagli ordini aperti e dall'ultimo prezzo disponibile. Se il prezzo manca si usa il
     * prezzo di acquisto, cosi la view resta stabile anche con dati mercato incompleti.
     */
    public function attachMarketMetrics(array $portfolioId): array
    {
        $open = model(OrderModel::class)->findOpenByPortfolio((int) $portfolioId['portfolio_id']);//ordini aperti del portafoglio

        $mv = 0;
        $unreal = 0.0;

        foreach ($open as $o) {
            $latest = model(PriceModel::class)->getLatestForListing($o['ticker'], $o['mic']);
            $currentPrice = $latest['price'] ?? null;

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
