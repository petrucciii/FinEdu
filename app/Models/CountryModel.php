<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'country_code';
    
    //allowed fields for insert and update operations
    protected $allowedFields = ['country_code', 'country', 'id_user', 'active'];

    //automatic update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //read only active records
    public function fread()
    {
        try {
            return $this->select('country_code, country')->where('active', 1)->findAll();
        } catch (Exception $e) {
            return false;
        }
    }

    //create a new record returning boolean
    public function fcreate(array $data)
    {
        try {
            return $this->insert($data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //update an existing record returning boolean
    public function fupdate(string $id, array $data)
    {
        try {
            return $this->update($id, $data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //logical delete returning boolean
    public function fdelete(string $id)
    {
        try {
            return $this->update($id, ['active' => 0]) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}