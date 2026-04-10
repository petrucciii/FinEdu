<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ExchangeModel extends Model
{
    protected $table = 'exchanges';
    protected $primaryKey = 'mic';

    //campi abilitati per insert e update
    protected $allowedFields = ['mic', 'full_name', 'short_name', 'country_code', 'opening_hour', 'closing_hour', 'currency_code', 'id_user', 'active'];

    //timestamp automatici
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    //serve a findAll() per restiture array associativi e non oggetti
    protected $returnType = 'array';

    //legge i record con join su tabelle dizionario
    public function fread()
    {
        try {
            return $this->select('exchanges.*, countries.country, currencies.description as currency_desc, currencies.symbol')
                ->join('countries', 'countries.country_code = exchanges.country_code', 'left')
                ->join('currencies', 'currencies.currency_code = exchanges.currency_code', 'left')
                ->where('exchanges.active', 1)
                ->findAll();
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
            return $this->update($id, ['active' => 0]);
        } catch (Exception $e) {
            return false;
        }
    }
}