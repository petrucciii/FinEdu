<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    
    protected $allowedFields = ['role', 'id_user', 'active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    protected $returnType = 'array';

    //read 
    public function fread()
    {
        try {
            return $this->select('role_id, role')->where('active', 1)->findAll();
        } catch (Exception $e) {
            return false;
        }
    }

    //insert
    public function fcreate(array $data)
    {
        try {
            return $this->insert($data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //update
    public function fupdate(int $id, array $data)
    {
        try {
            return $this->update($id, $data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //soft delete
    public function fdelete(int $id)
    {
        try {
            return $this->update($id, ['active' => 0]) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}