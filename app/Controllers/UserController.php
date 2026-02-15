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
            echo view("users/viewProfile", );
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
}