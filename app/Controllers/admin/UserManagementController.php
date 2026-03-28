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

            $page = $this->request->getGet('page') ?? 1;
            $roleId = $this->request->getGet('role_id');
            $levelId = $this->request->getGet('level_id');
            
            //validate ordering
            $order = $this->request->getGet('order');
            $orderType = $this->request->getGet('order_type');
            
            $validOrder = null;
            $validOrderType = null;
            
            if ($order && in_array(trim($order), $allowedOrderFields)) {
                $validOrder = trim($order);
            }
            if ($orderType && in_array(trim($orderType), ['ASC', 'DESC'])) {
                $validOrderType = trim($orderType);
            }

            $isExport = $this->request->getGet('export') ? true : false;

            //call model method to handle logic
            $result = $userModel->searchAndPaginate(
                $where, 
                $roleId, 
                $levelId, 
                $validOrder, 
                $validOrderType, 
                $page, 
                $isExport
            );

            //if is export mode pass everything at once not page by page
            if ($isExport) {
                $columns = ['user_id', 'first_name', 'last_name', 'email', 'role_id', 'level_id', 'created_at'];
                self::toCSV($result, $columns);
            }

            //paginate results
            $users = $result['users'];
            $pager = $result['pager'];

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
        $user = $userModel->fread(['users.user_id' => $userId]);
        $roles = model(RoleModel::class)->fread();

        if ($this->session->has('logged') && $this->session->get('role_id') == 1 && isset($user[0])) {
            unset($user[0]['password']);
            //return user data as json and all roles to populate dropdown 
            return $this->response->setJSON(['user' => $user[0], 'roles' => $roles]);
        }

        return redirect()->to('/');
    }

    public function editColumn($userId)
    {
        $allowedColumns = ['first_name', 'last_name', 'email', 'role_id'];

        $model = model(UserModel::class);
        //if is admin and fields have been inserted
        if ($this->session->has('logged') && $this->session->get('role_id') == 1 && $this->request->getPost('edit') && $this->request->getPost('new_value')) {
            if (in_array(trim($this->request->getPost('edit')), $allowedColumns)) {
                
                $data = [
                    trim($this->request->getPost('edit')) => trim($this->request->getPost('new_value')),
                    'id_user_updated' => $this->session->get('user_id')
                ];

                if ($model->fupdate($userId, $data)) {
                    return redirect()->back()->with('alert', "Modifica Riuscita");
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

            $admin = $model->fread(['users.user_id' => $this->session->get('user_id')]);
            $hashAdminPassword = $admin[0]['password'];

            //admin's password needed to delete user
            if (password_verify($this->request->getPost('password'), $hashAdminPassword)) {
                if ($model->fdelete($userId)) { //logical delete user
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
                    'password' => $this->request->getPost('password'), //hashing is done in the model
                    'first_name' => ucwords(strtolower(trim($this->request->getPost('first_name')))),
                    'last_name' => ucwords(strtolower(trim($this->request->getPost('last_name')))),
                    'role_id' => (int) $this->request->getPost('role_id'),
                    'id_user_updated' => $this->session->get('user_id')
                ];

                if ($userModel->fcreate($data)) {
                    return redirect()->to('/admin/UserManagementController/index')->with('alert', "Inserimento Riuscito");
                } else {
                    return redirect()->to('/admin/UserManagementController/index')->with('alert', "Errore durante l'inserimento");
                }
            }
            return redirect()->to('/admin/UserManagementController/index')->with('alert', "Dati non validi");
        }
        return redirect()->to('/');
    }

    //return a CSV file
    public static function toCSV($mdArray, $columns)
    {
        //file specifics
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=users_export.csv');

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