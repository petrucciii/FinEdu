<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            //'logged' => $this->session->get('logged')
        ];
        echo view('Views/templates/header', $data);
        echo view('pages/viewHome', $data);
        echo view('Views/templates/footer', $data);
    }
}
