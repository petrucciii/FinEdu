<?php
namespace App\Controllers;

use App\Models\Molanguage;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {

        //has() check the existance of the key ('logged')
        $logged = $this->session->has('logged');

        $data = ['error' => ""];

        if ($this->request->getPost('email') && ($this->request->getPost('password'))) {
            echo $this->request->getPost('email') . ",  " . ($this->request->getPost('password'));
            $model = model(UserModel::class);
            //get user by mail
            $result = $model->fread(["email" => strtolower($this->request->getPost('email'))]);
            $user = $result[0];




            //check if exists
            if (!empty($user)) {
                //compare input password with user's hashed password

                // var_dump($user);
                // echo "<br>";
                // echo $this->request->getPost('password');
                // echo "<br>";
                // echo password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                // die;
                if (password_verify(trim($this->request->getPost('password')), $user['password'])) {
                    unset($user['password']);
                    $this->session->set('logged', true);
                    $this->session->set($user);
                    return redirect()->to('/AuthController/profile');
                } else {
                    $data['error'] = "Password Errata!";
                }
            } else {
                $data['error'] = "Utente Non Trovato!";
            }

            echo view("templates/header", $data);
            echo view("pages/viewHome");
            echo view("templates/footer");
        }

        return redirect()->to("/");

    }


    //singup

}
