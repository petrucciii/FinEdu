<?php

namespace App\Commands;

use App\Libraries\YahooFinanceService;
use App\Models\ListingModel;
use App\Models\PriceModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**COMANDO PER TASK SCHEDULER: [path php.exe] spark prices:update 
 * ASSICURARSI DI AVERE extension curl e intl attive nel .ini 
 * CONFIGURARE task scheduler ogni 15 minuti per tempo indefinito*/

/* */

//Command in codeigniter non viene eseguito via web ma nel terminale.
//in particolare questo commadn viene definito cron job, viene, cioè, eseguito automaticamente ogni 15 minuti aggiornando il database
class UpdatePrices extends BaseCommand
{
    protected $group = 'FinEdu';
    protected $name = 'prices:update';//nome del comando che viene eseguito nel terminale
    protected $description = 'Scarica quotazioni Yahoo e inserisce righe nella tabella prices';

    public function run(array $params)
    {
        $listings = model(ListingModel::class)->findAllActiveForQuotes();//prende tutti listings presenti
        $yahoo = new YahooFinanceService();//richiama la liberiria per recuperare dati da yahoo
        $priceModel = model(PriceModel::class);

        $ok = 0;
        $fail = 0;

        //per ogni listing
        if (!$listings) {
            CLI::write("Market closed", 'yellow');
            return;
        }
        foreach ($listings as $row) {
            $sym = YahooFinanceService::listingToYahooSymbol($row['ticker'], $row['mic']);//trasforma il listing in simbolo yahoo
            $q = $yahoo->getQuoteData($sym);//recupera i dati da yahoo finance

            if ($q === null || $q['price'] === null) {//se non ci sono dati
                CLI::write("Skip {$row['ticker']}/{$row['mic']} ({$sym}) — nessun dato", 'yellow');
                $fail++;
                continue;
            }

            if ($priceModel->insertPrice($row['ticker'], $row['mic'], $q['price'])) {//inserisce i dati nel database
                CLI::write("OK {$row['ticker']}/{$row['mic']} = {$q['price']}", 'green');
                $ok++;
            } else {
                CLI::write("DB error {$row['ticker']}/{$row['mic']}", 'red');
                $fail++;
            }

            usleep(150000);
        }

        CLI::write("Completato: {$ok} aggiornati, {$fail} saltati/errati.", 'white');
    }
}
