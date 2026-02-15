<?php
namespace App\Controllers;

use App\Models\Molanguage;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {

        $logged = $this->session->has('logged');

        $data = [];

        if ($this->request->getPost('email') && ($this->request->getPost('password')) && !$logged) {
            $model = model(UserModel::class);
            //get user by mail
            $result = $model->fread(["email" => strtolower($this->request->getPost('email'))]);
            $user = $result[0];




            //check if exists
            if (!empty($user)) {
                //compare input password with user's hashed password
                if (password_verify(trim($this->request->getPost('password')), $user['password'])) {
                    unset($user['password']);
                    $this->session->set('logged', true);
                    $this->session->set($user);
                    return redirect()->to('/UserController/profile');
                } else {
                    $data['error'] = "Password Errata!";
                }
            } else {
                $data['error'] = "Utente Non Trovato!";
            }

            echo view("templates/header", $data);
            echo view("pages/viewHome");
            echo view("templates/footer");
            return;
        }

        return redirect()->to("/");

    }


    //logout
    public function logout()
    {
        $logged = $this->session->has('logged');
        if ($logged) {
            $this->session->remove('logged');
        }

        return redirect()->to("/");
    }
}
