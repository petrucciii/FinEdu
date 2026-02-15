<?php
namespace App\Controllers;

use App\Models\Molanguage;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function profile()
    {
        if ($this->session->has('logged')) {
            echo view("templates/header");
            echo view("pages/viewProfile");
            echo view("templates/footer");
            return;
        }
        return redirect()->to('/');
    }

    public function editColumn()
    {
        $allowedColumns = ['first_name', 'last_name', 'email'];

        $model = model(UserModel::class);
        //if is logged and fields have been inserted
        if ($this->session->has('logged') && $this->request->getPost('edit') && $this->request->getPost('new_value')) {
            if (in_array(trim($this->request->getPost('edit')), $allowedColumns)) {
                $data = [
                    'user_id' => $this->session->get('user_id'),
                    trim($this->request->getPost('edit')) => trim($this->request->getPost('new_value'))
                ];

                if ($model->fupdate($data)) {
                    $this->session->set(trim($this->request->getPost('edit')), trim($this->request->getPost('new_value')));
                    return redirect()->back()->with('alert', "Modifica Riuscita");
                } else {
                    return redirect()->back()->with('alert', 'Modifica non avvenuta');
                }

            } else {
                return redirect()->back()->with('alert', 'Si è verificato un problema');
            }

        }
        return redirect()->to('/');
    }

    public function editPassword()
    {
        $model = model(UserModel::class);
        if (
            $this->request->getPost('password') &&
            $this->request->getPost('new_password') &&
            $this->request->getPost('repeat_password') &&
            $this->session->has('logged')
        ) {
            //new password and repeat match
            if ($this->request->getPost('new_password') == $this->request->getPost('repeat_password')) {
                if ($this->request->getPost('new_password') != $this->request->getPost('password')) {
                    $user = $model->fread(['user_id' => $this->session->get('user_id')]);
                    $hashPassword = $user[0]['password'];
                    //password validation
                    if (password_verify($this->request->getPost('password'), $hashPassword)) {
                        $data = [
                            'user_id' => $this->session->get('user_id'),
                            'password' => $this->request->getPost('new_password')
                        ];

                        if ($model->fupdate($data)) {
                            return redirect()->back()->with('alert', 'Password Modificata!');
                        } else {
                            return redirect()->back()->with('alert', 'Password Non Modificata!');
                        }
                    } else {
                        return redirect()->back()->with('alert', 'Password Errata!');
                    }
                } else {
                    return redirect()->back()->with('alert', 'La nuova password non può essere uguale a quella corrente!');
                }
            } else {
                return redirect()->back()->with('alert', 'La nuova password non corrisponde!');
            }
        }

        return redirect()->to('/');
    }

    public function delete()
    {
        $model = model(UserModel::class);
        if ($this->request->getPost('password') && $this->session->has('logged')) {
            $user = $model->fread(['user_id' => $this->session->get('user_id')]);
            $hashPassword = $user[0]['password'];
            //password validation
            if (password_verify($this->request->getPost('password'), $hashPassword)) {
                if ($model->fdelete(['user_id' => $this->session->get('user_id')])) {//delete user
                    $this->session->remove([
                        'user_id',
                        'first_name',
                        'last_name',
                        'email',
                        'experience',
                        'level',
                        'role',
                        'logged'
                    ]);
                    return redirect()->to('/')->with('alert', 'Profilo eliminato!');

                } else {
                    return redirect()->back()->with('alert', 'Profilo non eliminato!');
                }
            } else {
                return redirect()->back()->with('alert', 'Password Errata!');
            }
        }
        return redirect()->to('/');
    }
}