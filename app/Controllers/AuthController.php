<?php
namespace App\Controllers;

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
            $result = $model->fread(["email" => strtolower(trim($this->request->getPost('email')))]);





            //check if exists
            if (!empty($result)) {
                $user = $result[0];
                //compare input password with user's hashed password
                if (password_verify(trim($this->request->getPost('password')), $user['password'])) {
                    unset($user['password']);
                    $this->session->set('logged', true);
                    $this->session->set($user);
                    return redirect()->to('/UserController/profile');
                } else {
                    $data['login_error'] = "Password Errata!";
                }
            } else {
                $data['login_error'] = "Utente Non Trovato!";
            }

            echo view("templates/header", $data);
            echo view("pages/viewHome");
            echo view("templates/footer");
            return;
        }

        return redirect()->to("/");

    }

    //singup
    public function singup()
    {

        if (
            $this->request->getPost('email') &&
            $this->request->getPost('first_name') &&
            $this->request->getPost('last_name') &&
            $this->request->getPost('password') &&
            !$this->session->has('logged')//if not already logged
        ) {
            $model = model(UserModel::class);
            $data = [];

            $user = [
                'first_name' => ucwords(strtolower(trim($this->request->getPost('first_name')))), //first letter of each word uppercase
                'last_name' => ucwords(strtolower(trim($this->request->getPost('last_name')))),
                'email' => strtolower(trim($this->request->getPost('email'))),
                'password' => $this->request->getPost('password')
            ];


            if ($model->fcreate($user)) {
                $data['signup_success'] = true;
            } else {
                $data['signup_error'] = "Registrazione Non Riuscita";
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
