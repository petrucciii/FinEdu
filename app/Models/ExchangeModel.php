<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ExchangeModel extends Model
{
    protected $table = 'exchanges';
    protected $primaryKey = 'mic';



    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT mic, short_name, full_name, country_code, currency_code, opening_hour, closing_hour, active FROM " . $this->table; //. "JOIN countries USING(country_code) JOIN currencies USING(currency_code)";

        try {
            return $db->query($sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

