<?php

namespace App\Libraries;

//libreria per recuperare quotazioni da Yahoo Finance, una libereria in codeigniter puo essere utilizzata in tutto il progetto
class YahooFinanceService
{
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    //lista di suffissi per mic
    private static function yahooSuffixForMic(string $mic): string
    {
        $m = strtoupper(trim($mic));
        switch ($m) {
            case 'XMIL':
            case 'MTAA':
                return '.MI';
            case 'XETR':
            case 'XFRA':
                return '.DE';
            case 'XPAR':
                return '.PA';
            case 'XLON':
                return '.L';
            case 'XAMS':
                return '.AS';
            case 'XSWX':
                return '.SW';
            case 'XMAD':
                return '.MC';
            default:
                return '';
        }
    }

    //trasformazione da mic a simbolo yahoo
    public static function listingToYahooSymbol(string $ticker, string $mic): string
    {
        $t = strtoupper(trim($ticker));
        $suffix = self::yahooSuffixForMic($mic);

        return $suffix !== '' ? $t . $suffix : $t;
    }

    /**
     * @return array{symbol: string, timestamp: ?int, price: ?float}|null
     */
    public function getQuoteData(string $yahooSymbol): ?array
    {
        //endpoint
        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/'
            . rawurlencode($yahooSymbol)
            . '?interval=1d&range=5d';

        $ctx = stream_context_create([
            'http' => [
                'header' => 'User-Agent: ' . self::UA . "\r\n", //headers per identificarsi come browser
                'timeout' => 12,
            ],
            'ssl' => [ //disabilita la verifica del certificato SSL per evitare errori
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        //recupera i dati da Yahoo Finance, @ sopprime gli errori
        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (empty($data['chart']['result'][0])) {
            return null;
        }

        $result = $data['chart']['result'][0];
        $meta = $result['meta'] ?? [];

        $price = $meta['regularMarketPrice'] ?? null;
        if ($price === null && !empty($result['indicators']['quote'][0]['close'])) {
            $closes = $result['indicators']['quote'][0]['close'];
            for ($i = count($closes) - 1; $i >= 0; $i--) {
                if ($closes[$i] !== null) {
                    $price = $closes[$i];
                    break;
                }
            }
        }

        //dati formattati per database
        return [
            'symbol' => $meta['symbol'] ?? $yahooSymbol,
            'timestamp' => $meta['regularMarketTime'] ?? null,
            'price' => $price !== null ? (float) $price : null,
        ];
    }
}
