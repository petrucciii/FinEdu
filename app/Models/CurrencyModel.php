<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CurrencyModel extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'currency_code';



    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT currency_code, symbol, description FROM " . $this->table;

        try {
            return $db->query($sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

