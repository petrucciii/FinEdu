<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $this->session->set('logged', true);
        $data = [
            //'logged' => $this->session->get('logged')
        ];
        echo view('Views/templates/header.php', $data);
        echo view('pages/viewHome.php', $data);
        echo view('Views/templates/footer.php', $data);
    }
}
