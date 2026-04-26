<?php

namespace App\Controllers;

use App\Models\CompletedLessonModel;
use App\Models\EducationModuleModel;
use App\Models\LessonModel;
use App\Models\QuestionModel;
use App\Models\UserModel;

class EducationController extends BaseController
{
    //controlla che l'utente sia loggato prima di entrare nel percorso formativo
    private function loginRedirect()
    {
        if (!$this->session->has('logged')) {
            return redirect()->to('/')->with('alert', 'Accedi per continuare.');
        }

        return null;
    }

    public function index()
    {
        //carica la pagina principale con livello, xp e moduli disponibili
        if ($redirect = $this->loginRedirect()) {
            return $redirect;
        }

        //legge i dati reali dell'utente e calcola i progressi dalle lezioni completate
        $userId = (int) $this->session->get('user_id');
        $user = model(UserModel::class)->fread(['users.user_id' => $userId])[0] ?? [];
        $modules = model(EducationModuleModel::class)->findProgressForUser($userId);
        $modules = $this->enrichModuleStatuses($modules);

        //somma lezioni e completamenti per mostrare la progress bar generale
        $totalLessons = 0;
        $completedLessons = 0;
        foreach ($modules as $module) {
            $totalLessons += (int) $module['lesson_count'];
            $completedLessons += (int) $module['completed_count'];
        }

        echo view('templates/header');
        echo view('pages/viewEducation', [
            'adminSection' => false,
            'user' => $user,
            'modules' => $modules,
            'totalLessons' => $totalLessons,
            'completedLessons' => $completedLessons,
            'progressPercent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
        ]);
        echo view('templates/footer');
    }

    public function module($moduleId)
    {
        //apre un singolo modulo e prepara lezioni, quiz e stato di blocco
        if ($redirect = $this->loginRedirect()) {
            return $redirect;
        }

        //controlla che il modulo esista e sia attivo
        $moduleId = (int) $moduleId;
        $module = model(EducationModuleModel::class)->findActiveById($moduleId);
        if (!$module) {
            return redirect()->to('/EducationController/index')->with('alert', 'Modulo non trovato.');
        }

        $userId = (int) $this->session->get('user_id');
        $moduleStatus = $this->findModuleStatus($moduleId, $userId);
        //blocca l'accesso diretto via url ai moduli non ancora sbloccati
        if (!$moduleStatus || $moduleStatus['status'] === 'locked') {
            return redirect()->to('/EducationController/index')->with('alert', 'Completa i moduli precedenti per sbloccare questo contenuto.');
        }

        //prende le lezioni attive e assegna completata, disponibile o bloccata
        $completedIds = model(CompletedLessonModel::class)->getCompletedLessonIds($userId);
        $lessons = model(LessonModel::class)->findActiveForModule($moduleId);
        $lessons = $this->enrichLessonStatuses($lessons, $completedIds);

        //carica risposte solo per le lezioni di tipo quiz
        $questionModel = model(QuestionModel::class);
        foreach ($lessons as &$lesson) {
            $lesson['questions'] = [];
            if ($lesson['lesson_type'] === 'quiz') {
                //usa solo il primo set risposte: ogni quiz deve averne uno solo
                $questions = $questionModel->findWithAnswersByLesson((int) $lesson['id_lesson']);
                $lesson['questions'] = empty($questions) ? [] : [$questions[0]];
            }
        }
        unset($lesson);

        //apre automaticamente la prima lezione disponibile
        $openLessonId = null;
        foreach ($lessons as $lesson) {
            if ($lesson['status'] === 'available') {
                $openLessonId = (int) $lesson['id_lesson'];
                break;
            }
        }

        echo view('templates/header');
        echo view('pages/viewEducationModule', [
            'adminSection' => false,
            'module' => $module,
            'moduleStatus' => $moduleStatus,
            'lessons' => $lessons,
            'openLessonId' => $openLessonId,
        ]);
        echo view('templates/footer');
    }

    public function completeExplanation()
    {
        //completa una spiegazione sia via ajax sia come form normale di fallback
        if (!$this->session->has('logged')) {
            return $this->failEducationResponse('Accedi per continuare.', '/', 'warning', 401);
        }

        //verifica che la lezione sia davvero una spiegazione
        $lessonId = (int) $this->request->getPost('lesson_id');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson || $lesson['lesson_type'] !== 'explanation') {
            return $this->failEducationResponse('Lezione non valida.', '/EducationController/index');
        }

        $userId = (int) $this->session->get('user_id');
        //impedisce di completare lezioni ancora bloccate manipolando il form
        if (!$this->isLessonUnlocked($lessonId, $userId)) {
            return $this->failEducationResponse('Lezione ancora bloccata.', '/EducationController/module/' . (int) $lesson['id_module']);
        }

        //registra il completamento solo se non era gia presente
        $completedModel = model(CompletedLessonModel::class);
        if (!$completedModel->hasCompleted($userId, $lessonId)) {
            $completedModel->recordAttempt($userId, $lessonId, true);
        }

        //ritorna anche lo stato aggiornato per sbloccare subito la lezione successiva via ajax
        return $this->successEducationResponse(
            'Lezione completata.',
            '/EducationController/module/' . (int) $lesson['id_module'],
            [
                'lesson_id' => $lessonId,
                'completed' => true,
                'correct' => true,
                'attempts' => $completedModel->countAttempts($userId, $lessonId),
                'state' => $this->buildModuleState((int) $lesson['id_module'], $userId),
            ]
        );
    }

    public function submitQuiz()
    {
        //controlla una risposta quiz e salva sempre il tentativo nella tabella completed_lessons
        if (!$this->session->has('logged')) {
            return $this->failEducationResponse('Accedi per continuare.', '/', 'warning', 401);
        }

        //recupera la lezione e accetta solo lezioni specializzate come quiz
        $lessonId = (int) $this->request->getPost('lesson_id');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson || $lesson['lesson_type'] !== 'quiz') {
            return $this->failEducationResponse('Quiz non valido.', '/EducationController/index');
        }

        $userId = (int) $this->session->get('user_id');
        //evita che l'utente salti in avanti inviando manualmente un id lezione
        if (!$this->isLessonUnlocked($lessonId, $userId)) {
            return $this->failEducationResponse('Quiz ancora bloccato.', '/EducationController/module/' . (int) $lesson['id_module']);
        }

        $completedModel = model(CompletedLessonModel::class);
        //se il quiz e gia completato non aggiunge xp doppi
        if ($completedModel->hasCompleted($userId, $lessonId)) {
            return $this->successEducationResponse(
                'Quiz gia completato.',
                '/EducationController/module/' . (int) $lesson['id_module'],
                [
                    'lesson_id' => $lessonId,
                    'completed' => true,
                    'correct' => true,
                    'attempts' => $completedModel->countAttempts($userId, $lessonId),
                    'state' => $this->buildModuleState((int) $lesson['id_module'], $userId),
                ]
            );
        }

        $questions = model(QuestionModel::class)->findWithAnswersByLesson($lessonId);
        //un quiz senza domande o risposte non puo essere svolto
        if (empty($questions)) {
            return $this->failEducationResponse('Quiz non configurato.', '/EducationController/module/' . (int) $lesson['id_module']);
        }
        //considera solo il primo set: il quiz deve avere un solo set di quattro risposte
        $questions = [$questions[0]];
        if (count($questions[0]['answers']) !== 4) {
            return $this->failEducationResponse('Il quiz deve avere esattamente 4 risposte.', '/EducationController/module/' . (int) $lesson['id_module']);
        }

        //legge le risposte inviate dal form ajax
        $answersPost = $this->request->getPost('answers');
        $answersPost = is_array($answersPost) ? $answersPost : [];
        $allCorrect = true;
        $allAnswered = true;
        $experience = 0;

        foreach ($questions as $question) {
            //la riga questions rappresenta l'unico set di risposte della lezione quiz
            $questionId = (int) $question['id_question'];
            $selectedId = (int) ($answersPost[$questionId] ?? 0);
            $experience += (int) $question['experience'];

            if ($selectedId < 1) {
                $allAnswered = false;
                $allCorrect = false;
                continue;
            }

            $validAnswer = null;
            foreach ($question['answers'] as $answer) {
                //confronta l'id risposta scelto con le risposte attive nel db
                if ((int) $answer['id_answer'] === $selectedId) {
                    $validAnswer = $answer;
                    break;
                }
            }

            if (!$validAnswer || (int) $validAnswer['is_correct'] !== 1) {
                $allCorrect = false;
            }
        }

        //richiede una risposta per il set, altrimenti non salva il tentativo
        if (!$allAnswered) {
            return $this->failEducationResponse('Seleziona una risposta per ogni domanda.', '/EducationController/module/' . (int) $lesson['id_module']);
        }

        //salva tentativo e xp nella stessa transazione
        $db = db_connect();
        $db->transStart();
        $completedModel->recordAttempt($userId, $lessonId, $allCorrect);

        if ($allCorrect) {
            //gli xp vengono aggiunti solo alla prima risposta corretta del quiz non ancora completato
            $userModel = model(UserModel::class);
            $user = $userModel->find($userId);
            $newExperience = (int) ($user['experience'] ?? 0) + $experience;
            $userModel->update($userId, ['experience' => $newExperience]);
            $this->session->set('experience', $newExperience);
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->failEducationResponse('Errore durante il salvataggio del tentativo.', '/EducationController/module/' . (int) $lesson['id_module'], 'danger', 500);
        }

        if ($allCorrect) {
            //risposta corretta: il client ajax aggiorna badge, progresso e lezione successiva
            return $this->successEducationResponse(
                'Risposta corretta. Hai ottenuto ' . $experience . ' XP.',
                '/EducationController/module/' . (int) $lesson['id_module'],
                [
                    'lesson_id' => $lessonId,
                    'completed' => true,
                    'correct' => true,
                    'experience_gained' => $experience,
                    'attempts' => $completedModel->countAttempts($userId, $lessonId),
                    'state' => $this->buildModuleState((int) $lesson['id_module'], $userId),
                ]
            );
        }

        $hint = trim((string) ($lesson['hint'] ?? ''));
        $message = $hint !== '' ? 'Risposta errata. Suggerimento: ' . $hint : 'Risposta errata. Puoi riprovare.';

        //risposta errata: resta sulla stessa lezione e mostra subito il suggerimento
        if ($this->isAjaxRequest()) {
            return $this->response->setJSON([
                'success' => true,
                'completed' => false,
                'correct' => false,
                'lesson_id' => $lessonId,
                'message' => $message,
                'alert_type' => 'danger',
                'attempts' => $completedModel->countAttempts($userId, $lessonId),
                'state' => $this->buildModuleState((int) $lesson['id_module'], $userId),
            ]);
        }

        return redirect()->to('/EducationController/module/' . (int) $lesson['id_module'])
            ->with('alert', $message)
            ->with('alert_type', 'danger');
    }

    private function isAjaxRequest(): bool
    {
        //riconosce le chiamate fetch del file public/javascript/ajax/education.js
        return $this->request->isAJAX() || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false;
    }

    private function failEducationResponse(string $message, string $redirectTo, string $alertType = 'warning', int $status = 400)
    {
        //centralizza gli errori cosi lo stesso endpoint funziona sia con ajax sia senza javascript
        if ($this->isAjaxRequest()) {
            return $this->response->setStatusCode($status)->setJSON([
                'success' => false,
                'message' => $message,
                'alert_type' => $alertType,
                'redirect' => $redirectTo,
            ]);
        }

        return redirect()->to($redirectTo)
            ->with('alert', $message)
            ->with('alert_type', $alertType);
    }

    private function successEducationResponse(string $message, string $redirectTo, array $payload = [])
    {
        //centralizza le risposte positive e include dati extra per aggiornare la pagina senza refresh
        if ($this->isAjaxRequest()) {
            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => $message,
                'alert_type' => 'success',
            ], $payload));
        }

        return redirect()->to($redirectTo)
            ->with('alert', $message)
            ->with('alert_type', 'success');
    }

    private function buildModuleState(int $moduleId, int $userId): array
    {
        /*costruisce lo stato minimo che serve al javascript:
        progresso del modulo, stato di ogni lezione e xp utente.
        in questo modo il client non deve ricaricare tutta la pagina
        dopo una risposta corretta o una spiegazione completata.*/
        $moduleStatus = $this->findModuleStatus($moduleId, $userId);
        $completedIds = model(CompletedLessonModel::class)->getCompletedLessonIds($userId);
        $lessons = $this->enrichLessonStatuses(model(LessonModel::class)->findActiveForModule($moduleId), $completedIds);
        $user = model(UserModel::class)->find($userId);

        return [
            'module_progress' => [
                'progress_percent' => (int) ($moduleStatus['progress_percent'] ?? 0),
                'completed_count' => (int) ($moduleStatus['completed_count'] ?? 0),
                'lesson_count' => (int) ($moduleStatus['lesson_count'] ?? count($lessons)),
            ],
            'lessons' => array_map(static function ($lesson) {
                return [
                    'id_lesson' => (int) $lesson['id_lesson'],
                    'status' => $lesson['status'],
                    'completed' => (bool) $lesson['completed'],
                ];
            }, $lessons),
            'user_experience' => (int) ($user['experience'] ?? 0),
        ];
    }

    private function findModuleStatus(int $moduleId, int $userId): ?array
    {
        //cerca lo stato del singolo modulo dentro la lista progressiva gia calcolata
        $modules = $this->enrichModuleStatuses(model(EducationModuleModel::class)->findProgressForUser($userId));
        foreach ($modules as $module) {
            if ((int) $module['id_module'] === $moduleId) {
                return $module;
            }
        }

        return null;
    }

    private function enrichModuleStatuses(array $modules): array
    {
        /*calcola lo stato progressivo dei moduli.
        un modulo resta bloccato finche quello precedente non e completato.
        se non esiste un campo ordine nel db, l'ordine usato e quello di id_module.*/
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

    private function enrichLessonStatuses(array $lessons, array $completedIds): array
    {
        /*calcola lo stato progressivo delle lezioni nel modulo.
        una lezione diventa disponibile solo se tutte quelle precedenti
        risultano completate in completed_lessons.*/
        $previousCompleted = true;

        foreach ($lessons as &$lesson) {
            $lessonId = (int) $lesson['id_lesson'];
            $completed = in_array($lessonId, $completedIds, true);
            $lesson['completed'] = $completed;

            if ($completed) {
                $lesson['status'] = 'completed';
            } elseif ($previousCompleted) {
                $lesson['status'] = 'available';
            } else {
                $lesson['status'] = 'locked';
            }

            $previousCompleted = $previousCompleted && $completed;
        }
        unset($lesson);

        return $lessons;
    }

    private function isLessonUnlocked(int $lessonId, int $userId): bool
    {
        //verifica lato server che una lezione sia davvero disponibile per l'utente
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson) {
            return false;
        }

        $moduleStatus = $this->findModuleStatus((int) $lesson['id_module'], $userId);
        if (!$moduleStatus || $moduleStatus['status'] === 'locked') {
            return false;
        }

        $completedIds = model(CompletedLessonModel::class)->getCompletedLessonIds($userId);
        $lessons = model(LessonModel::class)->findActiveForModule((int) $lesson['id_module']);
        $previousCompleted = true;

        foreach ($lessons as $currentLesson) {
            $currentId = (int) $currentLesson['id_lesson'];
            if ($currentId === $lessonId) {
                return $previousCompleted || in_array($currentId, $completedIds, true);
            }

            if (!in_array($currentId, $completedIds, true)) {
                $previousCompleted = false;
            }
        }

        return false;
    }
}
