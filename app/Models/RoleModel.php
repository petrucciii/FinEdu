<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role';



    //returns an array with the roles: [role1, role2, ...] 
    public function fread()
    {
        $db = db_connect();


        $sql = "SELECT role FROM " . $this->table;

        try {
            $result = $db->query($sql)->getResultArray();
            return array_column($result, 'role');
        } catch (Exception $e) {
            return false;
        }
    }

}
