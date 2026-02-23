<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';



    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT role_id, role FROM " . $this->table;

        try {
            return $db->query($sql)->getResultArray();
        } catch (Exception $e) {
            return false;
        }
    }

}

