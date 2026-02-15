<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class UserModel extends Model
{
     // add user
     public function fcreate($data)
     {
          $db = db_connect();
          //query with placeholders
          $sql = 'INSERT INTO users VALUES (:user_id:, :first_name:, :last_name:, :email:, :password:, :experience:, :level:, :role:)';
          $par = [ //bind
               'user_id' => $data['user_id'],
               'first_name' => $data['first_name'],
               'last_name' => $data['last_name'],
               'email' => $data['email'],
               'password' => $data['password'],
               'experience' => $data['experience'],
               'level' => $data['level'],
               'role' => $data['role'],
          ];

          //execution
          $result = $db->query($sql, $par);
          return $result;
     }

     public function fread($data)
     {
          $db = db_connect();
          $sql = 'SELECT (user_id, first_name, last_name, email, experience, level, role) FROM users';


          //$data[... 'where' => ["id" => "1", "name" => "matt" ] ...]
          if (!empty($data['where'])) {
               $sql .= " WHERE ";
               $conditions = [];
               foreach ($data['where'] as $key => $value) {
                    $conditions[] = "$key = ':$value:'";
               }

               $sql .= implode(" AND ", $conditions);

          }
          $query = $db->query($sql, $data['where']);
          $dataset = $query->getResult();
          return $dataset;
     }

     // -------------------------------------------------------------------------------------     
     public function fupdate($data)
     {
          $db = db_connect();
          $sql = 'UPDATE users SET  first_name=:first_name:, last_name=:last_name:, email=:email:, password=:password:, experience=:experience:, level=:level:, role=:role: WHERE user_id=:user_id:';
          $par = [
               'user_id' => $data['user_id'],
               'first_name' => $data['first_name'],
               'last_name' => $data['last_name'],
               'email' => $data['email'],
               'password' => $data['password'],
               'experience' => $data['experience'],
               'level' => $data['level'],
               'role' => $data['role'],

          ];
          $result = $db->query($sql, $par);
          return $result;
     }

     // -------------------------------------------------------------------------------------     
     public function fdelete($data)
     {
          $db = db_connect();
          $sql = 'DELETE from users where user_id=:user_id:';
          $par = ['user_id' => $data['user_id']];
          $result = $db->query($sql, $par);
          return $result;
     }
}
