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

    /*
     * Cerca e impagina la lista utenti della gestione admin.
     *
     * Le join con roles e levels servono per restituire direttamente le label leggibili
     * da mostrare in tabella, evitando query aggiuntive dal controller o dal JavaScript.
     * Sono LEFT JOIN per non perdere un utente se, per dati sporchi o incompleti, manca
     * temporaneamente il record collegato nella tabella roles o levels.
     *
     * La ricerca usa un solo input per nome, cognome ed email. La query viene divisa in
     * parole e ogni parola viene cercata su tutte e tre le colonne: cosi "Mario Rossi",
     * "Rossi Mario" o una parte dell'email filtrano lo stesso utente.
     */
    public function searchAndPaginate($searchQuery, $roleId, $levelId, $orderColumn, $orderType, $page, $isExport)
    {
        $builder = $this->select('users.*, roles.role, levels.level')
                        ->join('levels', 'users.level_id = levels.level_id', 'left')
                        ->join('roles', 'users.role_id = roles.role_id', 'left')
                        ->where('users.active', 1);

        //cerca per email, nome o cognome usando token indipendenti nello stesso input.
        $tokens = preg_split('/\s+/', trim((string) $searchQuery), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            $builder->groupStart()
                    ->like('users.email', $token)
                    ->orLike('users.first_name', $token)
                    ->orLike('users.last_name', $token)
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

    //count users by role
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
