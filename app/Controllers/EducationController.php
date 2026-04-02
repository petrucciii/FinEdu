<?php

namespace App\Controllers;

class EducationController extends BaseController
{
    public function index()
    {
        echo view('templates/header');
        echo view('pages/viewEducation', ['adminSection' => false]);
        echo view('templates/footer');
    }
}
