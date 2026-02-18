<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserManagementController extends BaseController
{
    public function index()
    {
        if ($this->session->has('logged') && $this->session->get('role') == "admin") {

            echo view("templates/header");
            echo view("pages/admins/viewUserManagement");
            echo view("templates/footer");
            return;
        }

        return redirect()->to('/');
    }


    public function search($where = '')
    {
        $userModel = model(UserModel::class);

        //only admin can search users, otherwise redirect to home page
        if ($this->session->get('role') == 'admin' || $this->session->has('logged')) {



            //get page number by GET method, if not present set it to 1
            $page = $this->request->getGet('page') ?? 1;


            $builder = $userModel;

            if (!empty(trim($where))) {
                //search users by email, first name or last name with like operator, so partial matches are allowed, for example if query is "ma" it will match "Mario", "Matteo", "Rossi Mario" and so on...
                $builder = $builder
                    ->groupStart()//open parenthesis for where conditions
                    ->like('email', $where)
                    ->orLike('first_name', $where)
                    ->orLike('last_name', $where)
                    ->groupEnd();//close parenthesis
            }


            //paginate results, 10 users per page, page number is passed as parameter, pagination managed in frontedn
            $users = $builder->paginate(10, 'default', $page);
            $pager = $userModel->pager;

            return $this->response->setJSON([
                'users' => $users,
                //pagination data to manage pagination in frontend
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

        if ($this->session->has('logged') && $this->session->get('role') == "admin" && $user[0]) {
            unset($user[0]['password']);
            //return user data as json and all roles to populate dropdown 
            return $this->response->setJSON(['user' => $user[0], 'roles' => $roles]);
        }

        return redirect()->to(uri: '/');
    }

    public function editColumn($userId)
    {
        $allowedColumns = ['first_name', 'last_name', 'email', 'role'];

        $model = model(UserModel::class);
        //if is admin and fields have been inserted
        if ($this->session->has('logged') && $this->session->get('role') == "admin" && $this->request->getPost('edit') && $this->request->getPost('new_value')) {
            if (in_array(trim($this->request->getPost('edit')), $allowedColumns)) {
                $data = [
                    'user_id' => $userId,
                    trim($this->request->getPost('edit')) => trim($this->request->getPost('new_value'))
                ];

                if ($model->fupdate($data)) {
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
        if ($this->request->getPost('password') && $this->session->has('logged') && $this->session->get('role') == "admin") {

            $admin = $model->fread(['user_id' => $this->session->get('user_id')]);
            $hashAdminPassword = $admin[0]['password'];

            //admin's password needed to delete user
            if (password_verify($this->request->getPost('password'), $hashAdminPassword)) {
                if ($model->fdelete(['user_id' => $userId])) {//delete user
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

}