<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    //colonne abilitate insert update
    protected $allowedFields = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'experience',
        'level_id',
        'role_id',
        'active',
        'id_user_updated'
    ];

    //timestamp automatici
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //create
    public function fcreate(array $data)
    {
        try {
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            return $this->insert($data) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    //read e filtri
    public function fread(array $where = [])
    {
        try {
            $builder = $this->select('users.*, roles.role, levels.level')
                            ->join('levels', 'levels.level_id = users.level_id', 'left')
                            ->join('roles', 'roles.role_id = users.role_id', 'left')
                            ->where('users.active', 1);

            if (!empty($where)) {
                $builder->where($where);
            }

            return $builder->findAll();
        } catch (Exception $e) {
            return false;
        }
    }

    //update
    public function fupdate(int $id, array $data)
    {
        try {
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
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

    //cerca e impagina
    public function searchAndPaginate($searchQuery, $roleId, $levelId, $orderColumn, $orderType, $page, $isExport)
    {
        $builder = $this->select('users.*, roles.role, levels.level')
                        ->join('levels', 'users.level_id = levels.level_id', 'left')
                        ->join('roles', 'users.role_id = roles.role_id', 'left')
                        ->where('users.active', 1);

        //cerca per email, nome o cognome
        if (!empty(trim($searchQuery))) {
            $builder->groupStart()
                    ->like('users.email', trim($searchQuery))
                    ->orLike('users.first_name', trim($searchQuery))
                    ->orLike('users.last_name', trim($searchQuery))
                    ->groupEnd();
        }

        //fltra per ruolo
        if ($roleId != "all" && is_numeric($roleId)) {
            $builder->where('users.role_id', (int) $roleId);
        }

        //filtra per lievello
        if ($levelId != "all" && is_numeric($levelId)) {
            $builder->where('users.level_id', (int) $levelId);
        }

        //ordina
        if ($orderColumn && $orderType) {
            $builder->orderBy($orderColumn, $orderType);
        }
        
        //se da esportare in csv ritorna array unico
        if ($isExport) {
            return $builder->findAll();
        }

        //altrimenti ritonna impaginato
        return [
            'users' => $builder->paginate(10, 'default', $page),
            'pager' => $this->pager
        ];
    }

    //conta utenti per ruolo (dashboard)
    public function countUsersByRole(int $roleId)
    {
        try {
            return $this->where('role_id', $roleId)
                        ->where('active', 1)
                        ->countAllResults();
        } catch (Exception $e) {
            return 0;
        }
    }
}