<?php

namespace App\Controllers;

use App\Models\CompletedLessonModel;
use App\Models\EducationModuleModel;
use App\Models\LessonModel;
use App\Models\LevelModel;
use App\Models\QuestionModel;
use App\Models\UserModel;

class EducationController extends BaseController
{
    //controlla che l'utente sia loggato prima di entrare nel percorso formativo
    private function loginRedirect()
    {
        if (!$this->session->has('logged')) {
            return redirect()->to(base_url('/'))->with('alert', 'Accedi per continuare.');
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
            'initialTestAvailable' => $completedLessons === 0 && !$this->session->get('skip_initial_test') && model(QuestionModel::class)->countActiveWithAnswers() > 0,
        ]);
        echo view('templates/footer');
    }

    public function skipInitialTest()
    {
        //salta il test solo per la sessione corrente: non esiste una colonna persistente dedicata
        if ($redirect = $this->loginRedirect()) {
            return $redirect;
        }

        $this->session->set('skip_initial_test', true);

        return redirect()->to(base_url('EducationController/index'))->with('alert', 'Puoi iniziare direttamente dal primo modulo.');
    }

    public function initialTest()
    {
        /*
         * Test facoltativo iniziale.
         * Viene proposto solo a chi non ha ancora completamenti salvati, cosi non altera
         * un percorso gia iniziato. Le domande sono i quiz reali collegati alle lesson.
         */
        if ($redirect = $this->loginRedirect()) {
            return $redirect;
        }

        $userId = (int) $this->session->get('user_id');
        if (model(CompletedLessonModel::class)->countCompletedForUser($userId) > 0) {
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Il percorso risulta gia iniziato.');
        }

        $questions = model(QuestionModel::class)->findInitialTestQuestions(5);
        if (empty($questions)) {
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Test iniziale non disponibile.');
        }

        echo view('templates/header');
        echo view('pages/viewEducationInitialTest', [
            'questions' => $questions,
            'adminSection' => false,
        ]);
        echo view('templates/footer');
    }

    public function submitInitialTest()
    {
        //valuta il test iniziale e completa solo i moduli superati in modo conservativo
        if ($redirect = $this->loginRedirect()) {
            return $redirect;
        }

        $userId = (int) $this->session->get('user_id');
        if (model(CompletedLessonModel::class)->countCompletedForUser($userId) > 0) {
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Il percorso risulta gia iniziato.');
        }

        $questionIds = $this->request->getPost('question_ids');
        $questionIds = is_array($questionIds) ? $questionIds : [];
        $answersPost = $this->request->getPost('answers');
        $answersPost = is_array($answersPost) ? $answersPost : [];
        $questions = model(QuestionModel::class)->findInitialTestQuestionsByIds($questionIds);

        if (empty($questions)) {
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Test iniziale non valido.');
        }

        $score = 0;
        $experience = 0;
        $moduleScores = [];

        foreach ($questions as $question) {
            $moduleId = (int) $question['id_module'];
            if (!isset($moduleScores[$moduleId])) {
                $moduleScores[$moduleId] = ['total' => 0, 'correct' => 0];
            }
            $moduleScores[$moduleId]['total']++;

            $selectedId = (int) ($answersPost[(int) $question['id_question']] ?? 0);
            $isCorrect = false;
            foreach ($question['answers'] as $answer) {
                //confronta solo risposte attive ricaricate dal database
                if ((int) $answer['id_answer'] === $selectedId && (int) $answer['is_correct'] === 1) {
                    $isCorrect = true;
                    break;
                }
            }

            if ($isCorrect) {
                $score++;
                $moduleScores[$moduleId]['correct']++;
                $experience += $this->weightedQuestionExperience((int) $question['experience'], $moduleId);
            }
        }

        $modulesToComplete = $this->initialTestPassedModules($moduleScores);
        $completedLessons = 0;

        $db = db_connect();
        //transazione necessaria: test, lezioni completate, xp e livello devono restare coerenti
        $db->transStart();

        $completedModel = model(CompletedLessonModel::class);
        $lessonModel = model(LessonModel::class);
        foreach ($modulesToComplete as $moduleId) {
            $lessons = $lessonModel->findActiveForModule((int) $moduleId);
            foreach ($lessons as $lesson) {
                $lessonId = (int) $lesson['id_lesson'];
                if (!$completedModel->hasCompleted($userId, $lessonId)) {
                    $completedModel->recordAttempt($userId, $lessonId, true);
                    $completedLessons++;
                }
            }
        }

        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);
        $newExperience = (int) ($user['experience'] ?? 0) + $experience;
        $newLevelId = $this->estimateInitialTestLevelId($completedLessons);
        $userModel->update($userId, [
            'experience' => $newExperience,
            'level_id' => $newLevelId,
            'id_user_updated' => $userId,
        ]);

        //chiudo la transazione solo dopo tutti gli aggiornamenti collegati
        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->to(base_url('EducationController/index'))
                ->with('alert', 'Errore durante il salvataggio del test iniziale.')
                ->with('alert_type', 'danger');
        }

        $this->session->set('experience', $newExperience);
        $this->session->set('level_id', $newLevelId);
        if ($completedLessons > 0) {
            $this->session->remove('skip_initial_test');
        } else {
            //evita di riproporre subito il test se non sono stati superati moduli
            $this->session->set('skip_initial_test', true);
        }

        $message = 'Test completato: ' . $score . ' risposte corrette su ' . count($questions) . '.';
        if ($completedLessons > 0) {
            $message .= ' Sono state completate ' . $completedLessons . ' lezioni dei moduli superati.';
        }

        return redirect()->to(base_url('EducationController/index'))
            ->with('alert', $message)
            ->with('alert_type', 'success');
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
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Modulo non trovato.');
        }

        $userId = (int) $this->session->get('user_id');
        $moduleStatus = $this->findModuleStatus($moduleId, $userId);
        //blocca l'accesso diretto via url ai moduli non ancora sbloccati
        if (!$moduleStatus || $moduleStatus['status'] === 'locked') {
            return redirect()->to(base_url('EducationController/index'))->with('alert', 'Completa i moduli precedenti per sbloccare questo contenuto.');
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
                if (!empty($questions)) {
                    $lesson['experience'] = $this->weightedQuestionExperience((int) $questions[0]['experience'], (int) $lesson['id_module']);
                }
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
            $experience += $this->weightedQuestionExperience((int) $question['experience'], (int) $lesson['id_module']);

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
        //apro una transazione per salvare tutto insieme: se un passaggio fallisce non restano dati a meta'
        $db->transStart();
        $completedModel->recordAttempt($userId, $lessonId, $allCorrect);

        if ($allCorrect) {
            //gli xp vengono aggiunti solo alla prima risposta corretta del quiz non ancora completato
            $userModel = model(UserModel::class);
            $user = $userModel->find($userId);
            $newExperience = (int) ($user['experience'] ?? 0) + $experience;
            $newLevelId = $this->estimateLevelId($newExperience);
            $userModel->update($userId, [
                'experience' => $newExperience,
                'level_id' => $newLevelId,
                'id_user_updated' => $userId,
            ]);
            $this->session->set('experience', $newExperience);
            $this->session->set('level_id', $newLevelId);
        }

        //chiudo la transazione solo dopo tutti i controlli, cosi' il db resta coerente
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

        return redirect()->to(base_url('EducationController/module/' . (int) $lesson['id_module']))
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
                'redirect' => base_url(ltrim($redirectTo, '/')),
            ]);
        }

        return redirect()->to(base_url(ltrim($redirectTo, '/')))
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

        return redirect()->to(base_url(ltrim($redirectTo, '/')))
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

        foreach ($modules as &$module) {//& serve per modificare direttamente array originale
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

    private function initialTestPassedModules(array $moduleScores): array
    {
        /*
         * In assenza di soglie nel db usiamo una regola:
         * un modulo viene riconosciuto solo se tutte le sue domande presenti nel test
         * sono corrette, e solo in ordine progressivo di id_module.
         */
        $passed = [];
        $modules = model(EducationModuleModel::class)->findActiveOrdered();

        foreach ($modules as $module) {
            $moduleId = (int) $module['id_module'];
            $score = $moduleScores[$moduleId] ?? null;
            if (!$score || (int) $score['total'] === 0) {
                break;
            }

            if ((int) $score['correct'] === (int) $score['total']) {
                $passed[] = $moduleId;
                continue;
            }

            break;
        }

        return $passed;
    }

    private function estimateLevelId(int $experience): int
    {
        /*
         * I livelli dipendono dall'XP pesata raggiunta sul totale raggiungibile:
         * - ogni domanda vale experience * id_module, quindi i moduli con id piu alto pesano di piu
         * - a 1/3 dell'XP totale si passa a Intermedio
         * - a 2/3 dell'XP totale si passa ad Avanzato
         */
        $levels = model(LevelModel::class)->fread();
        if (empty($levels) || !is_array($levels)) {
            return 1;
        }

        usort($levels, static fn(array $a, array $b): int => (int) $a['level_id'] <=> (int) $b['level_id']);

        $beginnerId = $this->findLevelId($levels, ['principiante', 'principante']) ?? (int) ($levels[0]['level_id'] ?? 1);
        $intermediateId = $this->findLevelId($levels, ['intermedio'])
            ?? (int) ($levels[min(1, count($levels) - 1)]['level_id'] ?? $beginnerId);
        $advancedId = $this->findLevelId($levels, ['avanzato'])
            ?? (int) ($levels[min(2, count($levels) - 1)]['level_id'] ?? $intermediateId);

        $totalExperience = $this->totalReachableWeightedExperience();
        if ($totalExperience <= 0) {
            return $beginnerId;
        }

        if ($experience >= ($totalExperience * 2 / 3)) {
            return $advancedId;
        }

        if ($experience >= ($totalExperience / 3)) {
            return $intermediateId;
        }

        return $beginnerId;
    }

    private function estimateInitialTestLevelId(int $completedLessons): int
    {
        /*
         * Il test iniziale assegna un livello di base in base alla parte di percorso
         * riconosciuta come completata: 1/3 Intermedio, 2/3 Avanzato.
         */
        $levels = model(LevelModel::class)->fread();
        if (empty($levels) || !is_array($levels)) {
            return 1;
        }

        usort($levels, static fn(array $a, array $b): int => (int) $a['level_id'] <=> (int) $b['level_id']);

        $beginnerId = $this->findLevelId($levels, ['principiante', 'principante']) ?? (int) ($levels[0]['level_id'] ?? 1);
        $intermediateId = $this->findLevelId($levels, ['intermedio'])
            ?? (int) ($levels[min(1, count($levels) - 1)]['level_id'] ?? $beginnerId);
        $advancedId = $this->findLevelId($levels, ['avanzato'])
            ?? (int) ($levels[min(2, count($levels) - 1)]['level_id'] ?? $intermediateId);

        $totalLessons = model(EducationModuleModel::class)->countActiveLessons();
        if ($totalLessons <= 0) {
            return $beginnerId;
        }

        if ($completedLessons >= ($totalLessons * 2 / 3)) {
            return $advancedId;
        }

        if ($completedLessons >= ($totalLessons / 3)) {
            return $intermediateId;
        }

        return $beginnerId;
    }

    private function weightedQuestionExperience(int $experience, int $moduleId): int
    {
        return max(0, $experience) * max(1, $moduleId);
    }

    //calcola l'XP totale raggiungibile pesando ogni domanda con l'id del modulo, per stimare i livelli in modo proporzionale al percorso effettivo
    /**DA SPOSTARE NEL MODEL */
    private function totalReachableWeightedExperience(): int
    {
        $sql = 'SELECT COALESCE(SUM(weighted_experience), 0) AS total
            FROM (
                SELECT q.id_question, (MAX(q.experience) * MAX(m.id_module)) AS weighted_experience
                FROM questions q
                INNER JOIN lessons l ON l.id_lesson = q.id_lesson AND l.active = 1
                INNER JOIN modules m ON m.id_module = l.id_module AND m.active = 1
                INNER JOIN answers a ON a.id_question = q.id_question AND a.active = 1
                WHERE q.active = 1
                GROUP BY q.id_question
                HAVING COUNT(a.id_answer) = 4
            ) weighted_questions';

        return (int) (db_connect()->query($sql)->getRow('total') ?? 0);
    }

    private function findLevelId(array $levels, array $names): ?int
    {
        $names = array_map(static fn(string $name): string => strtolower($name), $names);
        foreach ($levels as $level) {
            $label = strtolower(trim((string) ($level['level'] ?? '')));
            if (in_array($label, $names, true)) {
                return (int) $level['level_id'];
            }
        }

        return null;
    }
}
