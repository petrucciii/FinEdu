<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\PortfolioModel;
use App\Models\SectorModel;
use App\Models\CurrencyModel;
use App\Models\CountryModel;
use App\Models\ExchangeModel;
use App\Models\AnalystConsensusModel;
use App\Models\FinancialDataModel;
use App\Models\BoardModel;
use App\Models\ShareholderModel;
use App\Models\ListingModel;
use App\Models\PriceModel;
use App\Models\NewsModel;


use App\Controllers\BaseController;

use Exception;
use Throwable;

//controller lato utente per la visualizzazione delle societa quotate.
//gestisce la lista societa, la ricerca ajax e la pagina dettaglio company
class CompanyController extends BaseController
{
    //pagina lista societa: renderizza la view con il flag adminSection=false
    //per distinguerla dalla versione admin
    public function index()
    {
        echo view("templates/header");
        echo view("pages/viewCompanyList", ['adminSection' => false]);
        echo view("templates/footer");
    }

    //endpoint ajax per la ricerca paginata delle societa.
    //riceve la query dalla barra di ricerca e il numero pagina via GET.
    //restituisce json con array companies + dati paginazione
    public function search($query = '')
    {
        $companyModel = model(CompanyModel::class);

        //recupera il numero di pagina dal parametro GET, default 1
        $page = $this->request->getGet('page') ?? 1;

        //delega al model la logica di ricerca e paginazione
        $result = $companyModel->searchAndPaginate($query, $page);

        $companies = $result['companies'];
        $pager = $result['pager'];

        return $this->response->setJSON([
            'companies' => $companies,
            'pagination' => [
                'currentPage' => $pager->getCurrentPage(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'pageCount' => $pager->getPageCount()
            ]
        ]);
    }

    //pagina dettaglio societa: carica tutti i dati necessari per la viewCompany.
    //include prezzo corrente, grafico, consensus analisti, bilanci, cda, azionariato e news
    public function viewCompany($isin)
    {
        $companyModel = model(CompanyModel::class);
        $consensusModel = model(AnalystConsensusModel::class);
        $financialDataModel = model(FinancialDataModel::class);
        $boardModel = model(BoardModel::class);
        $shareholderModel = model(ShareholderModel::class);

        //recupera i dati base della societa tramite isin
        try {
            $isin = trim($isin);
            $company = $companyModel->getCompanyByISIN($isin);
        } catch (Exception $e) {
            return redirect()->to('/CompanyController/index')->with('alert', 'Società non trovata');
        }

        //carica dati correlati dalla rispettive tabelle
        $consensus = $consensusModel->findConsensusPerCompany($isin);
        $financialData = $financialDataModel->findDataPerCompany($isin);
        $board = $boardModel->findBoardPerCompany($isin);
        $shareholders = $shareholderModel->findShareholdersPerCompany($isin);

        //recupera l'ultimo prezzo dal main listing della societa.
        $mainListing = self::getMainExchange($isin);
        if ($mainListing)
            $latestPriceLine = model(PriceModel::class)->getLatestForListing($mainListing['ticker'], $mainListing['mic']);


        //prepara prezzo e data ultimo aggiornamento per la view
        $displayPrice = $latestPriceLine['price'] ?? '-';
        $displayPriceUpdate = $latestPriceLine['date'] ?? '-';
        if ($displayPriceUpdate !== '-') {
            $displayPriceUpdate = date('d/m/Y H:i', strtotime($displayPriceUpdate));
        }

        //ultime 5 notizie collegate alla societa
        $latestNews = model(NewsModel::class)->findLatestForCompany($isin, 5);

        //dati per il grafico prezzi, default 1Y
        $chartData = $this->getChartData($isin, '1Y');

        //assembla tutti i dati per la view
        $data = [
            'company' => $company,
            'consensus' => $consensus,
            'averageRating' => self::getAverageRating($consensus),
            'averageTargetPrice' => self::getAverageTargetPrice($consensus),
            'displayPrice' => $displayPrice,
            'displayPriceUpdate' => $displayPriceUpdate,
            'latestNews' => $latestNews,
            'chartLabels' => $chartData['labels'],
            'chartValues' => $chartData['values'],
            'financialData' => self::buildFinancialArray($financialData),
            'board' => $board,
            'shareholders' => $shareholders,
            'listings' => model(ListingModel::class)->findActiveByIsin($isin),
            'mainListing' => $mainListing,
            'userPortfolios' => model(PortfolioModel::class)->findActiveByUser(session()->get('user_id')),
            'countries' => model(CountryModel::class)->findAll(),
            'currencies' => model(CurrencyModel::class)->findAll(),
            'sectors' => model(SectorModel::class)->findAll(),
            'exchanges' => model(ExchangeModel::class)->findAll(),
            'adminSection' => false,
        ];

        echo view("templates/header");
        echo view("pages/viewCompany", $data);
        echo view("templates/footer");
    }

    //endpoint ajax per aggiornare il grafico quando l'utente cambia il range (3M, 6M, 1Y, MAX).
    //chiamato da priceChart.js tramite fetch
    public function getChartDataJSON($isin, $range = '1Y')
    {
        return $this->response->setJSON($this->getChartData($isin, $range));
    }

    //costruisce labels e values per chart.js a partire dai prezzi giornalieri.
    //le date vengono formattate in italiano con mese abbreviato (es. "03 Mar 25").
    //per il range MAX usa solo "mese anno" (es. "Mar 2024") per leggibilita
    private function getChartData($isin, $range)
    {
        $mainListing = self::getMainExchange($isin);

        if (!$mainListing) {
            return false;
        }


        //usa il primo listing come riferimento per i prezzi
        $prices = model(PriceModel::class)->getDailySeriesForChart($mainListing['ticker'], $mainListing['mic'], $range);

        $labels = [];
        $values = [];

        //mesi abbreviati in italiano per formattare le ascisse del grafico
        $mesiIt = [1 => 'Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];

        foreach ($prices as $p) {
            $dt = new \DateTime($p['d']);
            $month = $mesiIt[(int) $dt->format('n')];

            //per MAX mostra solo mese+anno, per gli altri range giorno+mese+anno corto
            if ($range === 'MAX') {
                $labels[] = $month . ' ' . $dt->format('Y');
            } else {
                $labels[] = $dt->format('d') . ' ' . $month . ' ' . $dt->format('y');
            }

            $values[] = (float) $p['price'];
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public static function getMainExchange($isin)
    {

        $company = model(CompanyModel::class)->getCompanyByISIN($isin);
        $listings = model(ListingModel::class)->findActiveByIsin($isin);

        if (empty($company) || empty($listings) || !$company || !$listings) {
            return false;
        }
        $mainExchange = $company['main_exchange'];

        $mainListing = "";

        foreach ($listings as $l) {
            if ($l['mic'] == $mainExchange) {
                $mainListing = $l;
                break;
            }
        }

        return $mainListing;
    }

    //endpoint ajax per caricare il body di una news nel modal di lettura (viewCompany).
    //verifica che la news sia collegata all'isin richiesto
    public function newsBody($isin, $id)
    {
        $newsModel = model(NewsModel::class);
        $news = $newsModel->getBodyJson($id, $isin);
        return $this->response->setJSON($news);
    }

    //calcola il rating medio degli analisti per la societa.
    //converte BUY=1, HOLD=0, SELL=-1, fa la media e riconverte in stringa.
    //se non ci sono consensus restituisce "N/A"
    private static function getAverageRating($consensus)
    {
        $ratings = [];

        foreach ($consensus as $c) {
            //converte il rating testuale in valore numerico
            if ($c['rating'] == "BUY")
                $rating = 1;
            else if ($c['rating'] == "HOLD")
                $rating = 0;
            else
                $rating = -1;

            array_push($ratings, $rating);
        }

        try {
            $avgRating = array_sum($ratings) / count($ratings);

            //riconverte il valore numerico arrotondato in stringa leggibile
            if (round($avgRating) == 1)
                $avgRating = "BUY";
            else if (round($avgRating) == 0)
                $avgRating = "HOLD";
            else
                $avgRating = "SELL";

            return $avgRating;
        } catch (Throwable $e) {
            return "N/A";
        }
    }

    //calcola la media dei target price degli analisti.
    //restituisce il valore numerico o "N/A" se non ci sono consensus
    private static function getAverageTargetPrice($consensus)
    {
        $targetPrices = [];

        foreach ($consensus as $c) {
            array_push($targetPrices, $c['target_price']);
        }

        try {
            $avgTP = array_sum($targetPrices) / count($targetPrices);
            return format_number($avgTP, 2);
        } catch (Throwable $e) {
            return "N/A";
        }
    }

    //trasforma i dati finanziari grezzi dal db in una struttura pivot per la tabella bilancio.
    //la struttura risultante ha:
    // - 'years': array [anno => "anno tipo"] per le colonne della tabella
    // - 'rows': array [campo => ['label' => ..., 'values' => [anno => valore]]] per le righe
    // - 'currency_code': valuta dei dati
    //calcola anche campi derivati: ebit, tax_rate e net_margin che non sono nel db
    private static function buildFinancialArray($result)
    {
        //etichette italiane per ogni voce del bilancio
        $labels = [
            'revenues' => 'Ricavi',
            'amortizations_depretiations' => 'Ammortamenti e Svalutazioni',
            'ebit' => 'EBIT (Risultato Operativo)',
            'interests' => 'Interessi',
            'income_taxes' => 'Imposte sul Reddito',
            'net_profit' => 'Utile Netto',
            'net_margin' => 'Margine Netto (%)',
            'tax_rate' => 'Tax Rate (%)',
            'free_cash_flow' => 'Free Cash Flow',
            'capex' => 'CAPEX',
            'dividends' => 'Dividendi',
            'net_debt' => 'Debito Netto',
            'share_number' => 'Numero Azioni'
        ];

        $years = [];
        $rows = [];

        //inizializza la struttura base con tutte le voci e valori vuoti
        foreach ($labels as $key => $label) {
            $rows[$key] = [
                'label' => $label,
                'values' => []
            ];
        }

        if (empty($result) || !is_array($result)) {
            return false;
        }

        //prende la valuta dal primo record
        $firstRow = reset($result);
        $currencyCode = $firstRow['currency_code'] ?? 'N/A';

        foreach ($result as $row) {
            //sostituisce null con '-' per la visualizzazione
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $row[$key] = '-';
                }
            }

            if (!isset($row['year']) || !isset($row['type'])) {
                return false;
            }

            //calcola i campi derivati non presenti nel db
            $net_profit = (float) ($row['net_profit'] ?? 0);
            $income_taxes = (float) ($row['income_taxes'] ?? 0);
            $interests = (float) ($row['interests'] ?? 0);
            $revenues = (float) ($row['revenues'] ?? 0);

            //ebit = utile netto + imposte + interessi (formula semplificata bottom-up)
            $ebit = $net_profit + $income_taxes + $interests;

            //tax rate e net margin calcolati solo se il denominatore e valido
            $tax_rate = $ebit > 0 ? ($income_taxes / $ebit) * 100 : '-';
            $net_margin = $revenues != 0 ? ($net_profit / $revenues) * 100 : '-';

            $row['ebit'] = $ebit;
            $row['tax_rate'] = $tax_rate;
            $row['net_margin'] = $net_margin;

            //costruisce la label della colonna anno combinando anno e tipo (A, S, T)
            $yearKey = $row['year'];
            $type = $row['type'];
            $years[$yearKey] = trim($yearKey . " " . $type);

            //popola i valori per ogni voce del bilancio
            foreach ($labels as $key => $label) {
                $rows[$key]['values'][$yearKey] = $row[$key] ?? '-';
            }
        }

        return [
            'years' => $years,
            'rows' => $rows,
            'currency_code' => $currencyCode
        ];
    }
}