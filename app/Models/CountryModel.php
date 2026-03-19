<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'country_code';

    //automati update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT country_code, country role FROM " . $this->table;

        try {
            return $db->query($sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

