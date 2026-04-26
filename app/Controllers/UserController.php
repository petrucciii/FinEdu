<?php
namespace App\Controllers;

use App\Models\CompletedLessonModel;
use App\Models\EducationModuleModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function profile()
    {
        if ($this->session->has('logged')) {
            $userId = (int) $this->session->get('user_id');
            $user = model(UserModel::class)->fread(['users.user_id' => $userId])[0] ?? [];
            $modules = $this->enrichModuleStatuses(model(EducationModuleModel::class)->findProgressForUser($userId));
            $recentAttempts = model(CompletedLessonModel::class)->recentAttemptsForUser($userId, 6);

            $totalLessons = 0;
            $completedLessons = 0;
            foreach ($modules as $module) {
                $totalLessons += (int) ($module['lesson_count'] ?? 0);
                $completedLessons += (int) ($module['completed_count'] ?? 0);
            }

            echo view("templates/header");
            echo view("pages/viewProfile", [
                'user' => $user,
                'modules' => $modules,
                'recentAttempts' => $recentAttempts,
                'totalLessons' => $totalLessons,
                'completedLessons' => $completedLessons,
                'progressPercent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
            ]);
            echo view("templates/footer");
            return;
        }
        return redirect()->to('/');
    }

    public function editColumn()
    {
        $allowedColumns = ['first_name', 'last_name', 'email'];

        $model = model(UserModel::class);
        //controllo se campi inseriti e se loggato
        if ($this->session->has('logged') && $this->request->getPost('edit') && $this->request->getPost('new_value')) {
            if (in_array(trim($this->request->getPost('edit')), $allowedColumns)) {
                $data = [
                    trim($this->request->getPost('edit')) => trim($this->request->getPost('new_value'))
                ];

                if ($model->fupdate($this->session->get('user_id'), $data)) {
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
            //nuova password, controllando anche lato server se matchano
            if ($this->request->getPost('new_password') == $this->request->getPost('repeat_password')) {
                if ($this->request->getPost('new_password') != $this->request->getPost('password')) {
                    $user = $model->fread(['user_id' => $this->session->get('user_id')]);
                    $hashPassword = $user[0]['password'];
                    //validazione passweord
                    if (password_verify($this->request->getPost('password'), $hashPassword)) {
                        $data = [
                            'password' => $this->request->getPost('new_password')
                        ];

                        if ($model->fupdate($this->session->get('user_id'), $data)) {
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
            //validazione password
            if (password_verify($this->request->getPost('password'), $hashPassword)) {
                if ($model->fdelete((int) $this->session->get('user_id'))) {//delete user
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

    private function enrichModuleStatuses(array $modules): array
    {
        //Calcola stato e percentuale dei moduli come nella pagina Education.
        $previousCompleted = true;

        foreach ($modules as &$module) {
            $lessonCount = (int) ($module['lesson_count'] ?? 0);
            $completedCount = (int) ($module['completed_count'] ?? 0);
            $module['progress_percent'] = $lessonCount > 0 ? (int) round(($completedCount / $lessonCount) * 100) : 0;

            if (!$previousCompleted) {
                $module['status'] = 'locked';
            } elseif ($lessonCount > 0 && $completedCount >= $lessonCount) {
                $module['status'] = 'completed';
            } elseif ($completedCount > 0) {
                $module['status'] = 'in_progress';
            } else {
                $module['status'] = 'available';
            }

            $previousCompleted = $previousCompleted && ($lessonCount === 0 || $completedCount >= $lessonCount);
        }
        unset($module);

        return $modules;
    }
}
