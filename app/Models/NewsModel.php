<?php

namespace App\Models;

use CodeIgniter\Model;


class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'news_id';
    protected $allowedFields = [
        'newspaper_id',
        'headline',
        'subtitle',
        'body', //colonna BLOB nel db, contiene HTML formattato da quill
        'author',
        'date',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    //converte il campo body da BLOB (resource stream) a stringa UTF-8.
    //mysql puo restituire i BLOB come php resource invece che stringa,
    //quindi serve stream_get_contents per leggere il contenuto effettivo.
/*NON FUNZIONANTE */
    private static function convertBlobToString(&$row): void
    {
        if (!isset($row['body']))
            return;

        //se mysql restituisce il blob come resource stream
        if (is_resource($row['body'])) {
            $row['body'] = stream_get_contents($row['body']);
        }

        //assicura che il risultato sia una stringa valida
        if (!is_string($row['body'])) {
            $row['body'] = (string) $row['body'];
        }
    }

    //ricerca paginata delle news per la gestione admin.
    //join con newspapers e companies_news per mostrare fonte e loghi aziende collegate.
    //usa GROUP_CONCAT per aggregare loghi e nomi in una sola riga per news
    public function searchAndPaginate(string $searchQuery, int $page): array
    {
        //query base: seleziona i campi principali + loghi e nomi aggregati con separatore |||
        $builder = $this->db->table('news')
            ->select('news.news_id, news.headline, news.subtitle, news.author, news.date, news.active,
                newspapers.newspaper,
                GROUP_CONCAT(DISTINCT companies.logo_path ORDER BY companies.isin SEPARATOR "|||") AS logos_raw,
                GROUP_CONCAT(DISTINCT companies.name ORDER BY companies.isin SEPARATOR "|||") AS names_raw', false)
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->join('companies_news', 'companies_news.news_id = news.news_id', 'left')
            ->join('companies', 'companies.isin = companies_news.isin AND companies.active = 1', 'left')
            ->where('news.active', 1)
            ->groupBy('news.news_id');

        //filtro di ricerca su piu campi con OR (headline, subtitle, autore, fonte, azienda)
        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $builder->groupStart()
                ->like('news.headline', $searchQuery)
                ->orLike('news.subtitle', $searchQuery)
                ->orLike('news.author', $searchQuery)
                ->orLike('newspapers.newspaper', $searchQuery)
                ->orLike('companies.name', $searchQuery)
                ->groupEnd();
        }

        $builder->orderBy('news.date', 'DESC');
        /**IMPAGINAZIONE MANUALE (SENZA paginate()) */
        //conta il totale delle righe per la paginazione, necessario per la presenza di GROUP_CONCAT
        //paginate non supporta, infatti, query cosi complesse
        $countSql = 'SELECT COUNT(*) AS c FROM (' . $builder->getCompiledSelect(false) . ') _news_count';//subquery che conta tabella con notizie ritornate
        $total = (int) ($this->db->query($countSql)->getRow('c') ?? 0);

        //applica limite e offset per la pagina corrente
        $perPage = 10;
        $offset = max(0, ($page - 1) * $perPage);//indica il record da saltare, e quindi da quale partire per pagina corrente
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        $pageCount = max(1, (int) ceil($total / $perPage));

        return [
            'news' => $rows,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pageCount' => $pageCount,
            ],
        ];
    }

    //recupera tutti i dettagli di una news per il modal di modifica admin.
    //include il body (BLOB) e il nome del giornale
    public function findDetailForAdmin(int $newsId): ?array
    {
        $row = $this->select('news.*, newspapers.newspaper')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->where('news.news_id', $newsId)
            ->where('news.active', 1)
            ->first();

        if ($row) {
            //converte il body da BLOB/resource a stringa leggibile
            self::convertBlobToString($row);
        }

        return $row ?: null;
    }

    //recupera le ultime N notizie collegate a una societa (per il box laterale viewCompany).
    //non include il body per performance (serve solo titolo/data/fonte)
    public function findLatestForCompany(string $isin, int $limit = 3): array
    {
        return $this->select('news.news_id, news.headline, news.subtitle, news.author, news.date, newspapers.newspaper')
            ->join('companies_news', 'companies_news.news_id = news.news_id')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->where('companies_news.isin', $isin)
            ->where('news.active', 1)
            ->orderBy('news.date', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    //recupera tutti i campi di una news (headline, subtitle, body, author, date, newspaper) per il modal di lettura lato utente (viewCompany).
    //verifica che la news sia effettivamente collegata all'isin richiesto
    public function getBodyJson(int $newsId, string $isin): ?array
    {
        $row = $this->select('news.headline, news.subtitle, news.body, news.author, news.date, newspapers.newspaper')
            ->join('companies_news', 'companies_news.news_id = news.news_id')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')//left join cosi anche se non ha news collegate ritorna qualcosa
            ->where('news.news_id', $newsId)
            ->where('companies_news.isin', $isin)
            ->where('news.active', 1)
            ->first();

        if ($row) {
            //converte il body da BLOB/resource a stringa leggibile
            self::convertBlobToString($row);
        }

        return $row ?: null;
    }
}
