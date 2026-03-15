<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'country_code';



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

