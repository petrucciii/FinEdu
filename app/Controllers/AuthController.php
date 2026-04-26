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
            //normalizza l'email prima della ricerca per evitare duplicati solo per maiuscole/spazi
            $result = $model->fread(["email" => strtolower(trim($this->request->getPost('email')))]);





            //controlla se esiste
            if (!empty($result)) {
                $user = $result[0];
                //password_verify confronta la password chiara con l'hash salvato da UserModel::fcreate
                if (password_verify(trim($this->request->getPost('password')), $user['password'])) {
                    unset($user['password']);
                    //salva in sessione i campi utente usati da header, permessi e view profilo
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

    //signup: crea un nuovo utente pubblico, il model si occupa dell'hash password
    public function singup()
    {

        if (
            $this->request->getPost('email') &&
            $this->request->getPost('first_name') &&
            $this->request->getPost('last_name') &&
            $this->request->getPost('password') &&
            !$this->session->has('logged')//se non già loggato
        ) {
            $model = model(UserModel::class);
            $data = [];

            //nomi normalizzati per avere iniziali/ricerche coerenti nelle tabelle admin
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
