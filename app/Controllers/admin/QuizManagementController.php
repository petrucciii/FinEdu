<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnswerModel;
use App\Models\LessonModel;
use App\Models\QuestionModel;

class QuizManagementController extends BaseController
{
    //verifica che l'utente corrente sia admin
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    public function index()
    {
        //mostra tutti i quiz attivi configurati come lezioni specializzate
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $data = [
            'quizzes' => model(LessonModel::class)->findQuizLessonsForAdmin(),
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewQuizManagement', $data);
        echo view('templates/footer');
    }

    public function editor($lessonId)
    {
        //apre l'editor risposte per una singola lezione quiz
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        //la lezione deve esistere ed essere di tipo quiz
        $lessonId = (int) $lessonId;
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson || $lesson['lesson_type'] !== 'quiz') {
            return redirect()->to('/admin/QuizManagementController/index')->with('alert', 'Quiz non trovato.');
        }

        $questions = model(QuestionModel::class)->findWithAnswersByLesson($lessonId);

        echo view('templates/header');
        echo view('pages/admins/viewQuizEditor', [
            'lesson' => $lesson,
            'question' => $questions[0] ?? null,
            'adminSection' => true,
        ]);
        echo view('templates/footer');
    }

    public function updatePrompt()
    {
        //aggiorna il testo della domanda usando title, description e hint di lessons
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $lessonId = (int) $this->request->getPost('id_lesson');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson || $lesson['lesson_type'] !== 'quiz') {
            return redirect()->back()->with('alert', 'Quiz non valido.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));
        $hint = trim((string) $this->request->getPost('hint'));
        if ($title === '' || $description === '') {
            return redirect()->back()->with('alert', 'Titolo e domanda sono obbligatori.');
        }

        model(LessonModel::class)->update($lessonId, [
            'title' => $title,
            'description' => $description,
            'hint' => $hint,
            'id_user' => $this->session->get('user_id'),
        ]);

        return redirect()->back()->with('alert', 'Quiz aggiornato.')->with('alert_type', 'success');
    }

    public function createQuestion()
    {
        //crea l'unico set di quattro risposte della lezione quiz
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $lessonId = (int) $this->request->getPost('id_lesson');
        $lesson = model(LessonModel::class)->findDetail($lessonId);
        if (!$lesson || $lesson['lesson_type'] !== 'quiz') {
            return redirect()->back()->with('alert', 'Quiz non valido.');
        }

        $existingQuestions = model(QuestionModel::class)->countActiveByLesson($lessonId);
        if ($existingQuestions > 0) {
            return redirect()->back()->with('alert', 'Un quiz puo avere un solo set di 4 risposte.');
        }

        $prepared = $this->prepareAnswers();
        //prepareanswers garantisce quattro risposte e una sola corretta
        if (!$prepared['valid']) {
            return redirect()->back()->with('alert', $prepared['message']);
        }

        //transazione per salvare insieme questions e answers
        $db = db_connect();
        //apro una transazione per salvare tutto insieme: se un passaggio fallisce non restano dati a meta'
        $db->transStart();

        $questionModel = model(QuestionModel::class);
        $questionModel->insert([
            'id_lesson' => $lessonId,
            'experience' => max(0, (int) $this->request->getPost('experience')),
            'id_user' => $this->session->get('user_id'),
            'active' => 1,
        ]);
        $questionId = (int) $questionModel->getInsertID();
        if ($questionId < 1) {
            //faccio rollback per annullare anche le operazioni gia' fatte in questa transazione
            $db->transRollback();
            return redirect()->back()->with('alert', 'Errore durante il salvataggio.')->with('alert_type', 'danger');
        }
        model(AnswerModel::class)->insertManyForQuestion(
            $questionId,
            $prepared['answers'],
            (int) $this->session->get('user_id')
        );

        //chiudo la transazione solo dopo tutti i controlli, cosi' il db resta coerente
        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Errore durante il salvataggio.')->with('alert_type', 'danger');
        }

        return redirect()->back()->with('alert', 'Risposte inserite.')->with('alert_type', 'success');
    }

    public function updateQuestion()
    {
        //sostituisce le quattro risposte mantenendo lo stesso id_question
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $questionId = (int) $this->request->getPost('id_question');
        $question = model(QuestionModel::class)->findActive($questionId);
        if (!$question) {
            return redirect()->back()->with('alert', 'Domanda non valida.');
        }

        $prepared = $this->prepareAnswers();
        if (!$prepared['valid']) {
            return redirect()->back()->with('alert', $prepared['message']);
        }

        $db = db_connect();
        //apro una transazione per salvare tutto insieme: se un passaggio fallisce non restano dati a meta'
        $db->transStart();

        //aggiorna gli xp del set risposte
        model(QuestionModel::class)->update($questionId, [
            'experience' => max(0, (int) $this->request->getPost('experience')),
            'id_user' => $this->session->get('user_id'),
        ]);

        //disattiva le vecchie risposte prima di inserire quelle nuove
        model(AnswerModel::class)->deactivateByQuestion($questionId, (int) $this->session->get('user_id'));

        model(AnswerModel::class)->insertManyForQuestion(
            $questionId,
            $prepared['answers'],
            (int) $this->session->get('user_id')
        );

        //chiudo la transazione solo dopo tutti i controlli, cosi' il db resta coerente
        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('alert', 'Aggiornamento non riuscito.')->with('alert_type', 'danger');
        }

        return redirect()->back()->with('alert', 'Risposte aggiornate.')->with('alert_type', 'success');
    }

    public function deleteQuestion()
    {
        //impedisce di rimuovere l'unico set risposte del quiz
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $questionId = (int) $this->request->getPost('id_question');
        $question = model(QuestionModel::class)->findActive($questionId);
        if (!$question) {
            return redirect()->back()->with('alert', 'Domanda non valida.');
        }

        return redirect()->back()->with('alert', 'Un quiz deve mantenere il suo unico set di 4 risposte.');
    }

    private function prepareAnswers(): array
    {
        /*normalizza le risposte inviate dal form.
        il db ha solo answers.answer e answers.is_correct,
        quindi il testo della domanda resta nella lesson e qui si controlla
        che ci siano quattro risposte e una sola risposta corretta.*/
        $answerTexts = $this->request->getPost('answer_text');
        $answerTexts = is_array($answerTexts) ? $answerTexts : [];
        $correctIndex = $this->request->getPost('correct_index');
        $answers = [];

        foreach ($answerTexts as $index => $text) {
            //ignora le righe lasciate vuote dall'admin
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }

            $answers[] = [
                'answer' => $text,
                'is_correct' => ((string) $index === (string) $correctIndex) ? 1 : 0,
            ];
        }

        if (count($answers) !== 4) {
            return ['valid' => false, 'message' => 'Inserisci esattamente 4 risposte.'];
        }

        $correctCount = 0;
        foreach ($answers as $answer) {
            //conta le risposte corrette per applicare il vincolo del quiz
            $correctCount += (int) $answer['is_correct'];
        }

        if ($correctCount !== 1) {
            return ['valid' => false, 'message' => 'Deve esserci una sola risposta corretta.'];
        }

        return ['valid' => true, 'answers' => $answers];
    }

}
