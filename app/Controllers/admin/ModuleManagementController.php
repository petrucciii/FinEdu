<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnswerModel;
use App\Models\CompletedLessonModel;
use App\Models\EducationModuleModel;
use App\Models\ExplanationModel;
use App\Models\LessonModel;
use App\Models\QuestionModel;

class ModuleManagementController extends BaseController
{
    //verifica che l'utente corrente sia admin
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    public function index()
    {
        //mostra la gestione admin di moduli e lezioni educative
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        //carica i moduli con contatori e poi aggancia le lezioni attive di ognuno
        $modules = model(EducationModuleModel::class)->findForAdmin();
        $lessonModel = model(LessonModel::class);
        foreach ($modules as &$module) {
            $module['lessons'] = $lessonModel->findActiveForModule((int) $module['id_module']);
        }
        unset($module);

        $data = [
            'modules' => $modules,
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewModuleManagement', $data);
        echo view('templates/footer');
    }

    public function progress()
    {
        /*
         * Mostra una pagina sola lettura con i progressi educativi.
         *
         * La pagina puo' essere aperta dalla gestione moduli (tutti gli utenti) oppure dal
         * modal gestione utente con ?user_id=...; in quel caso il model filtra lo stesso
         * riepilogo su un singolo utente senza creare una view duplicata.
         */
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        //ricerca e paginazione tengono leggera la tabella quando gli utenti crescono
        $page = (int) ($this->request->getGet('page') ?? 1);
        $search = (string) ($this->request->getGet('search') ?? '');
        $userId = (int) ($this->request->getGet('user_id') ?? 0);
        $progress = model(CompletedLessonModel::class)->progressByUsers($search, $page, $userId > 0 ? $userId : null);
        $totalLessons = model(EducationModuleModel::class)->countActiveLessons();

        echo view('templates/header');
        echo view('pages/admins/viewEducationProgress', [
            'rows' => $progress['users'],
            'pager' => $progress['pager'],
            'search' => trim($search),
            'selectedUserId' => $userId > 0 ? $userId : null,
            'totalLessons' => $totalLessons,
            'adminSection' => true,
        ]);
        echo view('templates/footer');
    }

    public function progressSearch($query = '')
    {
        /*
         * Endpoint AJAX per la tabella "Progressi utenti".
         *
         * È costruito come la ricerca news: il JavaScript invia testo e pagina, il model
         * cerca nel database e qui torniamo JSON con righe, paginazione e totale lezioni.
         * Così la ricerca non filtra solo la pagina già caricata, ma tutti gli utenti.
         */
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $userId = (int) ($this->request->getGet('user_id') ?? 0);
        $progress = model(CompletedLessonModel::class)->progressByUsers((string) $query, $page, $userId > 0 ? $userId : null);

        return $this->response->setJSON([
            'rows' => $progress['users'],
            'pagination' => $progress['pager'],
            'totalLessons' => model(EducationModuleModel::class)->countActiveLessons(),
        ]);
    }

    public function createModule()
    {
        //crea un modulo usando solo i campi reali della tabella modules
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        if ($name === '' || $description === '') {
            return redirect()->back()->with('alert', 'Nome e descrizione del modulo sono obbligatori.');
        }

        model(EducationModuleModel::class)->insert([
            'name' => $name,
            'description' => $description,
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ]);

        return redirect()->back()->with('alert', 'Modulo creato.')->with('alert_type', 'success');
    }

    public function updateModule()
    {
        //modifica nome e descrizione del modulo senza toccare i progressi utente
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $moduleId = (int) $this->request->getPost('id_module');
        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        if ($moduleId < 1 || $name === '' || $description === '') {
            return redirect()->back()->with('alert', 'Dati modulo non validi.');
        }

        model(EducationModuleModel::class)->update($moduleId, [
            'name' => $name,
            'description' => $description,
            'id_user' => $this->session->get('user_id'),
        ]);

        return redirect()->back()->with('alert', 'Modulo aggiornato.')->with('alert_type', 'success');
    }

    public function deleteModule()
    {
        //disattiva il modulo con soft delete tramite active = 0
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $moduleId = (int) $this->request->getPost('id_module');
        if ($moduleId < 1) {
            return redirect()->back()->with('alert', 'Modulo non valido.');
        }

        model(EducationModuleModel::class)->update($moduleId, [
            'active' => 0,
            'id_user' => $this->session->get('user_id'),
        ]);

        return redirect()->back()->with('alert', 'Modulo disattivato.')->with('alert_type', 'success');
    }

    public function createLesson()
    {
        //crea una lezione e la specializza subito come spiegazione oppure quiz
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        //i campi comuni finiscono sempre in lessons
        $moduleId = (int) $this->request->getPost('id_module');
        $type = (string) $this->request->getPost('lesson_type');
        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));
        $hint = trim((string) $this->request->getPost('hint'));

        $module = model(EducationModuleModel::class)->findActiveById($moduleId);
        //accetta solo moduli attivi e i due tipi previsti dallo schema
        if (!$module || !in_array($type, ['explanation', 'quiz'], true) || $title === '' || $description === '') {
            return redirect()->back()->with('alert', 'Dati lezione non validi.');
        }

        //transazione necessaria per non lasciare una lesson senza specializzazione
        $db = db_connect();
        $db->transStart();

        $lessonModel = model(LessonModel::class);
        $lessonModel->insert([
            'title' => $title,
            'description' => $description,
            'hint' => $hint,
            'id_module' => $moduleId,
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ]);
        $lessonId = (int) $lessonModel->getInsertID();
        if ($lessonId < 1) {
            $db->transRollback();
            return redirect()->back()->with('alert', 'Errore durante la creazione della lezione.')->with('alert_type', 'danger');
        }

        if ($type === 'explanation') {
            //se e spiegazione crea la riga nella tabella explanations
            $body = trim((string) $this->request->getPost('body'));
            if ($body === '') {
                $body = $description;
            }

            model(ExplanationModel::class)->insert([
                'id_lesson' => $lessonId,
                'body' => $body,
                'id_user' => $this->session->get('user_id'),
                'active' => 1,
            ]);
        } else {
            //se e quiz crea la prima riga questions con gli xp configurati
            model(QuestionModel::class)->insert([
                'id_lesson' => $lessonId,
                'experience' => max(0, (int) $this->request->getPost('experience')),
                'id_user' => $this->session->get('user_id'),
                'active' => 1,
            ]);
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Errore durante la creazione della lezione.')->with('alert_type', 'danger');
        }

        if ($type === 'quiz') {
            //dopo un quiz nuovo porta subito l'admin all'editor risposte
            return redirect()->to('/admin/QuizManagementController/editor/' . $lessonId)
                ->with('alert', 'Quiz creato. Inserisci le risposte.')
                ->with('alert_type', 'success');
        }

        return redirect()->back()->with('alert', 'Lezione creata.')->with('alert_type', 'success');
    }

    public function updateLesson()
    {
        //aggiorna i dati comuni della lezione e il body se e una spiegazione
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $lessonId = (int) $this->request->getPost('id_lesson');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));
        $hint = trim((string) $this->request->getPost('hint'));

        if (!$lesson || $title === '' || $description === '') {
            return redirect()->back()->with('alert', 'Dati lezione non validi.');
        }

        $db = db_connect();
        $db->transStart();

        model(LessonModel::class)->update($lessonId, [
            'title' => $title,
            'description' => $description,
            'hint' => $hint,
            'id_user' => $this->session->get('user_id'),
        ]);

        if ($lesson['lesson_type'] === 'explanation') {
            //il body appartiene alla tabella explanations, non alla tabella lessons
            $body = trim((string) $this->request->getPost('body'));
            if ($body === '') {
                $body = $description;
            }

            if (!empty($lesson['id_explanation'])) {
                //aggiorna la spiegazione gia esistente
                model(ExplanationModel::class)->update((int) $lesson['id_explanation'], [
                    'body' => $body,
                    'id_user' => $this->session->get('user_id'),
                ]);
            } else {
                //crea una spiegazione se la lesson ne era rimasta senza
                model(ExplanationModel::class)->insert([
                    'id_lesson' => $lessonId,
                    'body' => $body,
                    'id_user' => $this->session->get('user_id'),
                    'active' => 1,
                ]);
            }
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Aggiornamento non riuscito.')->with('alert_type', 'danger');
        }

        return redirect()->back()->with('alert', 'Lezione aggiornata.')->with('alert_type', 'success');
    }

    public function deleteLesson()
    {
        //disattiva una lezione e anche le righe specializzate collegate
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $lessonId = (int) $this->request->getPost('id_lesson');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson) {
            return redirect()->back()->with('alert', 'Lezione non valida.');
        }

        $db = db_connect();
        $db->transStart();

        //soft delete sulla parte comune della lezione
        model(LessonModel::class)->update($lessonId, [
            'active' => 0,
            'id_user' => $this->session->get('user_id'),
        ]);
        //soft delete sulle spiegazioni collegate
        model(ExplanationModel::class)->deactivateByLesson($lessonId, (int) $this->session->get('user_id'));

        //soft delete su domande e risposte collegate al quiz
        $questions = model(QuestionModel::class)->findByLesson($lessonId);
        foreach ($questions as $question) {
            model(QuestionModel::class)->update((int) $question['id_question'], [
                'active' => 0,
                'id_user' => $this->session->get('user_id'),
            ]);
            model(AnswerModel::class)->deactivateByQuestion(
                (int) $question['id_question'],
                (int) $this->session->get('user_id')
            );
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Eliminazione non riuscita.')->with('alert_type', 'danger');
        }

        return redirect()->back()->with('alert', 'Lezione disattivata.')->with('alert_type', 'success');
    }
}
