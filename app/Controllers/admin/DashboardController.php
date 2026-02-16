<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        if ($this->session->has('logged') && $this->session->get('role') == "admin") {
            echo view("templates/header");
            echo view("pages/admins/viewDashboard");
            echo view("templates/footer");
            return;
        }
        return redirect()->to('/');
    }
}