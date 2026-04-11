<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $modelUser = model(UserModel::class);
        if ($this->session->has('logged') && $this->session->get('role_id') == 1) {
            /* $data = [
                 'userCount' => $modelUser->countUsers(['role_id' => 2])
             ];*/
            echo view("templates/header");
            echo view("pages/admins/viewDashboard", /*$data*/);
            echo view("templates/footer");
            return;
        }
        return redirect()->to('/');
    }
}