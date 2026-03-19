<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CurrencyModel extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'currency_code';

    //automati update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

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

