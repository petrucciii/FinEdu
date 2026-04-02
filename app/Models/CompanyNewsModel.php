<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyNewsModel extends Model
{
    protected $table = 'companies_news';
    protected $allowedFields = ['news_id', 'isin', 'id_user'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    public function replaceLinks(int $newsId, array $isins, ?int $userId): void
    {
        $this->db->table($this->table)->where('news_id', $newsId)->delete();
        foreach ($isins as $isin) {
            $isin = trim((string) $isin);
            if ($isin === '') {
                continue;
            }
            $this->insert([
                'news_id' => $newsId,
                'isin' => $isin,
                'id_user' => $userId,
            ]);
        }
    }

    public function getIsinsForNews(int $newsId): array
    {
        $rows = $this->select('isin')->where('news_id', $newsId)->findAll();
        return array_column($rows, 'isin');
    }
}
