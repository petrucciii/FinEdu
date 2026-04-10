<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class LevelModel extends Model
{
    protected $table = 'levels';
    protected $primaryKey = 'level_id';

    //campi abilitati
    protected $allowedFields = ['level', 'id_user', 'active'];

    //automatici timesstamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';

    //serve a findAll() per restiture array associativi e non oggetti
    protected $returnType = 'array';
    //read
    public function fread()
    {
        try {
            return $this->select('level_id, level')->where('active', 1)->findAll();
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