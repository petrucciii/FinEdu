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

            $data['users'] = $userModel->paginate(5); // 5 utenti per pagina
            $data['pager'] = $userModel->pager;

            echo view("templates/header");
            echo view("pages/admins/viewUserManagement", $data);
            echo view("templates/footer");
            return;
        }

        return redirect()->to('/');
    }

}