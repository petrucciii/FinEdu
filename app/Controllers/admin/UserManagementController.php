<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserManagementController extends BaseController
{
    public function index()
    {
        $userModel = model(UserModel::class);

        if ($this->session->has('logged') && $this->session->get('role') == "admin") {

            //get paginated users with 10 users per page
            $data['users'] = $userModel->paginate(10);
            ///pass pager to view
            $data['pager'] = $userModel->pager;

            echo view("templates/header");
            echo view("pages/admins/viewUserManagement", $data);
            echo view("templates/footer");
            return;
        }

        return redirect()->to('/');
    }

}