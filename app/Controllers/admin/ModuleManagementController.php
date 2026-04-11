<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class ModuleManagementController extends BaseController
{
    public function index()
    {
        $data = [
            //'logged' => $this->session->get('logged')
        ];
        echo view('templates/header', $data);
        echo view('pages/admins/viewModuleManagement', $data);
        echo view('templates/footer', $data);
    }
}
