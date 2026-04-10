<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CurrencyModel extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'currency_code';
    
    //campi consentiti per insete e update
    protected $allowedFields = ['currency_code', 'description', 'symbol', 'id_user', 'active'];

    //timestamp automatici per inserte  update
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //read
    public function fread()
    {
        try {
            return $this->select('currency_code, symbol, description')->where('active', 1)->findAll();
        } catch (Exception $e) {
            return false;
        }
    }

    //create
    public function fcreate(array $data)
    {
        try {
            return $this->insert($data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //update
    public function fupdate(string $id, array $data)
    {
        try {
            return $this->update($id, $data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //soft delete
    public function fdelete(string $id)
    {
        try {
            return $this->update($id, ['active' => 0]) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}