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
        'body',
        'author',
        'date',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function searchAndPaginate(string $searchQuery, int $page): array
    {
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

        $countSql = 'SELECT COUNT(*) AS c FROM (' . $builder->getCompiledSelect(false) . ') _news_count';
        $total = (int) ($this->db->query($countSql)->getRow('c') ?? 0);

        $perPage = 10;
        $offset = max(0, ($page - 1) * $perPage);
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

    public function findDetailForAdmin(int $newsId): ?array
    {
        $row = $this->select('news.*, newspapers.newspaper')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->where('news.news_id', $newsId)
            ->where('news.active', 1)
            ->first();

        if ($row && isset($row['body']) && !is_string($row['body'])) {
            $row['body'] = (string) $row['body'];
        }

        return $row ?: null;
    }

    public function findLatestForCompany(string $isin, int $limit = 3): array
    {
        return $this->db->table('news')
            ->select('news.news_id, news.headline, news.subtitle, news.author, news.date, newspapers.newspaper')
            ->join('companies_news', 'companies_news.news_id = news.news_id')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->where('companies_news.isin', $isin)
            ->where('news.active', 1)
            ->orderBy('news.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getBodyJson(int $newsId, string $isin): ?array
    {
        $row = $this->db->table('news')
            ->select('news.news_id, news.headline, news.subtitle, news.body, news.author, news.date, newspapers.newspaper')
            ->join('companies_news', 'companies_news.news_id = news.news_id')
            ->join('newspapers', 'newspapers.newspaper_id = news.newspaper_id', 'left')
            ->where('news.news_id', $newsId)
            ->where('companies_news.isin', $isin)
            ->where('news.active', 1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
