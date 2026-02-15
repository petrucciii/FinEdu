<?php
namespace App\Controllers;

use App\Models\Molanguage;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function profile()
    {
        echo view("templates/header");
        echo view("users/viewProfile");
        echo view("templates/footer");
    }
}