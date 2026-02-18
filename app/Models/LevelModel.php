<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class LevelModel extends Model
{
    protected $table = 'levels';
    protected $primaryKey = 'level';



    //returns an array with the levels: [level1, level2, ...] 
    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT level FROM " . $this->table;

        try {
            $result = $db->query($sql)->getResultArray();
            return array_column($result, 'level');
        } catch (Exception $e) {
            return false;
        }
    }

}
