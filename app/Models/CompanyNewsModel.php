<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyNewsModel extends Model
{
    //ci4 configurazione per model
    protected $table = 'companies_news';
    protected $allowedFields = ['news_id', 'isin', 'id_user'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';



    /* Recupera gli ISIN associati a una notizia specifica.
     *
     * @param int $newsId L'ID della notizia.
     * @return array Un array di ISIN associati alla notizia.
     */
    public function getIsinsForNews(int $newsId): array
    {
        $rows = $this->select('isin')->where('news_id', $newsId)->findAll();
        return array_column($rows, 'isin');
    }
}
