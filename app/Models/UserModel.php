<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class UserModel extends Model
{
     private $allowedColumns = [
          'user_id',
          'first_name',
          'last_name',
          'email',
          'password',
          'experience',
          'level',
          'role'
     ];

     //create 
     public function fcreate(array $data)
     {
          $db = db_connect();
          // var_dump($data);
          // echo "<br>";

          //hash password
          $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
          //insert only given columns: intersect keys of data ["id"=>1, "name"=>"Mario"] with flipped allowedColumns (key and values swapped)
          $columns = array_intersect_key($data, array_flip($this->allowedColumns));
          $sqlColumns = implode(",", array_keys($columns));
          $sqlPlaceholders = ":" . implode(":,:", array_keys($columns)) . ":";

          $sql = "INSERT INTO users ($sqlColumns) VALUES ($sqlPlaceholders)";

          // var_dump($columns);
          // die;

          try {
               return $db->query($sql, $data);
          } catch (Exception $e) {
               return false;
          }

     }

     //read
     public function fread(array $where = [])
     {
          $db = db_connect();

          $sql = 'SELECT user_id, first_name, last_name, email, password, experience, level, role FROM users';
          $params = [];

          if (!empty($where)) {
               $conditions = [];
               foreach ($this->allowedColumns as $col) {
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
          foreach ($this->allowedColumns as $col) {//only allowed columns
               if ($col === 'user_id')
                    continue; //primary key cannot be updated
               if (isset($data[$col])) {//only columns that have to be updated
                    $setParts[] = "$col = :$col:";//placeholders
                    $params[$col] = $data[$col];
               }
          }

          $params['user_id'] = $data['user_id']; //where
          $sql = 'UPDATE users SET ' . implode(', ', array: $setParts) . ' WHERE user_id = :user_id:';

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
          $sql = 'DELETE FROM users WHERE user_id = :user_id:';
          $params = ['user_id' => $data['user_id']];
          try {
               return $db->query($sql, $params);
          } catch (Exception $e) {
               return false;
          }

     }

     public function countUsers($data)
     {
          $db = db_connect();
          $sql = 'SELECT COUNT(*) as count FROM users GROUP BY role HAVING role = :role:';
          $query = $db->query($sql, $data);
          return $query->getRow()->count;
     }
}
