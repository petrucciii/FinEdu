<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'country_code';
    
    
    protected $allowedFields = ['country_code', 'country', 'id_user', 'active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //prende solo record attivi
    public function fread()
    {
        try {
            return $this->select('country_code, country')->where('active', 1)->findAll();
        } catch (Exception $e) {
            return false;
        }
    }

    //crea nuovo paese
    public function fcreate(array $data)
    {
        try {
            return $this->insert($data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //update paese
    public function fupdate(string $id, array $data)
    {
        try {
            return $this->update($id, $data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //elimina logicamente
    public function fdelete(string $id)
    {
        try {
            return $this->update($id, ['active' => 0]) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}