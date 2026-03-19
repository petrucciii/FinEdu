<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;
use Kint\Renderer\RichRenderer;

class LevelModel extends Model
{
    protected $table = 'levels';
    protected $primaryKey = 'level_id';

    protected $allowedFields = ['level'];

    //automati update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';


    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT level_id, level FROM " . $this->table;

        try {
            return $db->query(sql: $sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

