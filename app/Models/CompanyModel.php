<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class CompanyModel extends Model
{
    //ci standard
    protected $table = 'companies';
    protected $primaryKey = 'isin';
    protected $allowedFields = [
          'name',
          'website',
          'logo_path',
          'country_code',
          'ea_code',
          'active',
     ];

    protected $useTimestamps = false;

    //not ci standard
    private $allColumns = [
        'isin',
        'name',
        'website',
        'logo_path',
        'country_code',
        'ea_code',
        'active',
     ];

     //create 
     public function fcreate(array $data)
     {
          $db = db_connect();

          //insert only given columns: intersect keys of data ["id"=>1, "name"=>"Mario"] with flipped allColumns (key and values swapped)
          $columns = array_intersect_key($data, array_flip($this->allColumns));
          $sqlColumns = implode(",", array_keys($columns));
          $sqlPlaceholders = ":" . implode(":,:", array_keys($columns)) . ":";

          $sql = "INSERT INTO {$this->table} ($sqlColumns) VALUES ($sqlPlaceholders)";

          // var_dump($columns);
          // die;

          try {
               return $db->query($sql, $data);
          } catch (Exception $e) {
               return false;
          }

     }

     //read
     public function fread($where = [])
     {
          $db = db_connect();

          $sql = "SELECT isin, name, website, logo_path, companies.country_code countries.country, companies.ea_code, sectors.description, active FROM " . $this->table . " JOIN countries USING(country_code) JOIN sectors USING(ea_code)";
          $params = [];

          if (!empty($where)) {
               $conditions = [];
               foreach ($this->allColumns as $col) {
                    if (isset($where[$col])) {
                         $conditions[] = "$col = :$col:";//placeholders
                         $params[$col] = $where[$col];
                    }
               }
               if (!empty($conditions)) {
                    $sql .= ' WHERE ' . implode(' AND ', $conditions);
               }
          }

          $query = $db->query($sql, $params);

          return $query->getResultArray();
     }

     //update
     public function fupdate(array $data)
     {
          $db = db_connect();

          // hash password
          if (!empty($data['password'])) {
               $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
          }


          $setParts = [];
          $params = [];
          foreach ($this->allColumns as $col) {//only allowed columns
               if ($col === 'isin')
                    continue; //primary key cannot be updated
               if (isset($data[$col])) {//only columns that have to be updated
                    $setParts[] = "$col = :$col:";//placeholders
                    $params[$col] = $data[$col];
               }
          }

          $params['isin'] = $data['isin']; //where
          $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', array: $setParts) . ' WHERE isin = :isin:';

          try {
               return $db->query($sql, $params);
          } catch (Exception $e) {
               return false;
          }

     }

     //delete
     public function fdelete(array $data)
     {
          $db = db_connect();
          $sql = 'DELETE FROM ' . $this->table . ' WHERE isin = :isin:';
          $params = ['isin' => $data['isin']];
          try {
               return $db->query($sql, $params);
          } catch (Exception $e) {
               return false;
          }

     }

     public function countUsers()
     {
          $db = db_connect();
          $sql = 'SELECT COUNT(*) as count FROM ' . $this->table . ' GROUP BY role_id HAVING role_id = :role_id:';
          $query = $db->query($sql);
          return $query->getRow()->count;
     }

}
