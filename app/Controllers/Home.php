<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $this->session->set('utente','pluto');
        $data=[];
    echo view('Views/pages/vwheader.php', $data);
    echo view('Views/pages/vwhome.php', $data);
    echo view('Views/pages/vwfooter.php', $data);
    }
}
