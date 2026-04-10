<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class SectorModel extends Model
{
    protected $table = 'sectors';
    protected $primaryKey = 'ea_code';
    
    //campi abilitati
    protected $allowedFields = ['ea_code', 'description', 'id_user', 'active'];

    //timestamp automatici
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //read
    public function fread()
    {
        try {
            return $this->select('ea_code, description')->where('active', 1)->findAll();
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