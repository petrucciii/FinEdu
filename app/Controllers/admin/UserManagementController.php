<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\LevelModel;
use Exception;

class UserManagementController extends BaseController
{

    public function index()
    {
        $levels = model(LevelModel::class)->fread();
        $roles = model(RoleModel::class)->fread();
        if ($this->session->has('logged') && $this->session->get('role_id') == 1) {

            echo view("templates/header");
            echo view("pages/admins/viewUserManagement", ['levels' => $levels, 'roles' => $roles]);
            echo view("templates/footer");
            return;
        }

        return redirect()->to('/');
    }


    public function search($where = '')
    {
        $userModel = model(UserModel::class);
        $allowedOrderFields = ['user_id', 'last_name, first_name', 'email', 'created_at'];

        //only admin can search users, otherwise redirect to home page
        if ($this->session->get('role_id') == 1 && $this->session->has('logged')) {

            //get page number by GET method, if not present set it to 1
            $page = $this->request->getGet('page') ?? 1;

            $builder = $userModel;
            $builder = $builder
                ->join('levels', "users.level_id = levels.level_id")
                ->join('roles', "users.role_id = roles.role_id");

            if (!empty(trim($where))) {
                //search users by email, first name or last name
                $builder = $builder
                    ->groupStart()
                    ->like('email', trim($where))
                    ->orLike('first_name', trim($where))
                    ->orLike('last_name', trim($where))
                    ->groupEnd();
            }

            //optional filters for role_id and level, if not "all" and present in get request and valid (exists in database)
            if ($this->request->getGet('role_id') != "all") {
                if (
                    $this->request->getGet('role_id')
                ) {
                    $builder = $builder->where('users.role_id', (int) $this->request->getGet('role_id'));
                }
            }

            if ($this->request->getGet('level_id') != "all") {
                if (
                    $this->request->getGet('level_id')
                ) {
                    $builder = $builder->where('users.level_id', (int) $this->request->getGet('level_id'));
                }
            }

            //optional order by, if present in get request and valid
            if (
                $this->request->getGet('order') &&
                in_array(
                    trim($this->request->getGet('order')),
                    $allowedOrderFields
                ) &&
                $this->request->getGet('order_type') &&
                in_array(
                    trim($this->request->getGet('order_type')),
                    ['ASC', 'DESC']
                )
            ) {
                $builder = $builder->orderBy(
                    $this->request->getGet(index: 'order'),
                    $this->request->getGet(index: 'order_type')
                );
            }
            //if is export mode pass everything at once not page by page
            if ($this->request->getGet('export')) {
                $users = $builder->findAll();

                $columns = ['user_id', 'first_name', 'last_name', 'email', 'role_id', 'level_id', 'created_at'];

                self::toCSV($users, $columns);
            }
            //paginate results, 10 users per page*/
            $users = $builder->paginate(10, 'default', $page);
            $pager = $userModel->pager;

            return $this->response->setJSON([
                'users' => $users,
                'pagination' => [
                    'currentPage' => $pager->getCurrentPage(),
                    'perPage' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'pageCount' => $pager->getPageCount()
                ]
            ]);
        }
        return redirect()->to('/');
    }


    public function settings($userId)
    {
        $userModel = model(UserModel::class);
        $user = $userModel->fread(['user_id' => $userId]);
        $roles = model(RoleModel::class)->fread();

        if ($this->session->has('logged') && $this->session->get('role_id') == 1 && isset($user[0])) {
            unset($user[0]['password']);
            //return user data as json and all roles to populate dropdown 
            return $this->response->setJSON(['user' => $user[0], 'roles' => $roles]);
        }

        return redirect()->to(uri: '/');
    }

    public function editColumn($userId)
    {
        $allowedColumns = ['first_name', 'last_name', 'email', 'role_id'];

        $model = model(UserModel::class);
        //if is admin and fields have been inserted
        if ($this->session->has('logged') && $this->session->get('role_id') == 1 && $this->request->getPost('edit') && $this->request->getPost('new_value')) {
            if (in_array(trim($this->request->getPost('edit')), $allowedColumns)) {
                $data = [
                    'user_id' => $userId,
                    trim($this->request->getPost('edit')) => trim($this->request->getPost('new_value'))
                ];

                if ($model->fupdate($data)) {
                    return redirect()->back()->with('alert', "Modifica Riuscita");//set 'alert' as a session variable
                } else {
                    return redirect()->back()->with('alert', 'Modifica non avvenuta');
                }
            } else {
                return redirect()->back()->with('alert', 'Si è verificato un problema');
            }
        }
        return redirect()->to('/');
    }

    public function delete($userId)
    {
        $model = model(UserModel::class);
        if ($this->request->getPost('password') && $this->session->has('logged') && $this->session->get('role_id') == 1) {

            $admin = $model->fread(['user_id' => $this->session->get('user_id')]);
            $hashAdminPassword = $admin[0]['password'];

            //admin's password needed to delete user
            if (password_verify($this->request->getPost('password'), $hashAdminPassword)) {
                if ($model->fdelete(['user_id' => $userId])) { //delete user
                    return redirect()->back()->with('alert', 'Profilo eliminato!');
                } else {
                    return redirect()->back()->with('alert', 'Profilo non eliminato!');
                }
            } else {
                return redirect()->back()->with('alert', 'Password Errata!');
            }
        }
        return redirect()->to('/');
    }

    public function add()
    {
        $userModel = model(UserModel::class);

        //is admin
        if ($this->session->has('logged') && $this->session->get('role_id') == 1) {
            if ($this->request->getPost('email') && $this->request->getPost('password') && $this->request->getPost('first_name') && $this->request->getPost('last_name') && $this->request->getPost('role_id')) {
                $data = [
                    'email' => strtolower(trim($this->request->getPost('email'))),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'first_name' => ucwords(strtolower(trim($this->request->getPost('first_name')))),
                    'last_name' => ucwords(strtolower(trim($this->request->getPost('last_name')))),
                    'role_id' => (int) $this->request->getPost('role_id')
                ];

                try {
                    $userModel->fcreate($data);
                    return redirect()->to('/admin/UserManagementController/index')->with('alert', "Inserimento Riuscito");
                } catch (Exception $e) {
                    return redirect()->to('/admin/UserManagementController/index')->with('alert', "Errore durante l'inserimento");
                }


            }
            return redirect()->to('/admin/UserManagementController/index')->with('alert', "Dati non validi");


        }

    }


    //return a CSV file
    /**
     * @param $mdArray multi dimensional array 
     * @param $columns fields that should be included
     */
    public static function toCSV($mdArray, $columns)
    {
        //file specifics
        header('Content-Type: text/csv; charset=utf-8');

        //output stream: send the response to an output window like an echo
        $output = fopen('php://output', 'w');

        //header of the csv
        fputcsv($output, $columns);

        //populate csv
        foreach ($mdArray as $user) {
            $row = [];
            foreach ($columns as $column) {
                //csv row
                $row[] = $user[$column] ?? '';
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
