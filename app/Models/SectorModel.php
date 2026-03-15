<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class SectorModel extends Model
{
    protected $table = 'sectors';
    protected $primaryKey = 'ea_code';



    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT ea_code, description FROM " . $this->table;

        try {
            return $db->query($sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

