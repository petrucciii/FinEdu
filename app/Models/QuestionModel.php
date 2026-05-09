<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    //tabella reale del set risposte associato a una lesson quiz
    protected $table = 'questions';
    protected $primaryKey = 'id_question';

    protected $allowedFields = [
        'id_lesson',
        'experience',
        'id_user',
        'active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_update';
    protected $returnType = 'array';

    public function findActive(int $questionId): ?array
    {
        //recupera un set risposte attivo.
        $row = $this->where('id_question', $questionId)
            ->where('active', 1)
            ->first();

        return $row ?: null;
    }

    public function findByLesson(int $lessonId): array
    {
        //tutte le domande collegate alla lesson, anche se gia disattivate.
        return $this->where('id_lesson', $lessonId)->findAll();
    }

    public function countActiveByLesson(int $lessonId): int
    {
        //un quiz deve avere un solo set attivo di quattro risposte.
        return (int) $this->where('id_lesson', $lessonId)
            ->where('active', 1)
            ->countAllResults();
    }

    public function findWithAnswersByLesson(int $lessonId): array
    {
        //carica le righe questions e ci aggancia le risposte attive
        $questions = $this->where('id_lesson', $lessonId)
            ->where('active', 1)
            ->orderBy('id_question', 'ASC')
            ->findAll();

        $answerModel = model(AnswerModel::class);
        foreach ($questions as &$question) {
            $question['answers'] = $answerModel->findActiveByQuestion((int) $question['id_question']);
        }
        unset($question);

        return $questions;
    }

    public function countActiveWithAnswers(): int
    {
        //conta solo i set quiz utilizzabili: quattro risposte attive come nel percorso normale
        $sql = 'SELECT COUNT(*) AS c FROM (
            SELECT q.id_question
            FROM questions q
            INNER JOIN lessons l ON l.id_lesson = q.id_lesson AND l.active = 1
            INNER JOIN modules m ON m.id_module = l.id_module AND m.active = 1
            INNER JOIN answers a ON a.id_question = q.id_question AND a.active = 1
            WHERE q.active = 1
            GROUP BY q.id_question
            HAVING COUNT(a.id_answer) = 4
        ) _initial_questions';

        return (int) ($this->db->query($sql)->getRow('c') ?? 0);
    }

    public function findInitialTestQuestions(int $limit = 30): array
    {
        /*
         * Il test iniziale usa i quiz reali gia presenti.
         * Non esiste una colonna testo domanda: per coerenza usiamo titolo e descrizione
         * della lesson collegata, che sono gia il contenuto mostrato nei quiz normali.
         */
        $questions = $this->db->table('questions q')
            ->select('q.id_question, q.id_lesson, q.experience, l.title, l.description, m.id_module, m.name AS module_name')
            ->join('lessons l', 'l.id_lesson = q.id_lesson AND l.active = 1')
            ->join('modules m', 'm.id_module = l.id_module AND m.active = 1')
            ->where('q.active', 1)
            ->orderBy('m.id_module', 'ASC')
            ->orderBy('q.id_question', 'ASC')
            ->get()
            ->getResultArray();

        return $this->pickMixedQuestions($this->attachAnswers($questions), $limit);
    }

    //chiamata quando viene inviato il test finale per ricontrollare
    public function findInitialTestQuestionsByIds(array $ids): array
    {
        //ricarica dal db le domande inviate dal form
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $questions = $this->db->table('questions q')
            ->select('q.id_question, q.id_lesson, q.experience, l.title, l.description, m.id_module, m.name AS module_name')
            ->join('lessons l', 'l.id_lesson = q.id_lesson AND l.active = 1')
            ->join('modules m', 'm.id_module = l.id_module AND m.active = 1')
            ->where('q.active', 1)
            ->whereIn('q.id_question', $ids)
            ->orderBy('m.id_module', 'ASC')
            ->orderBy('q.id_question', 'ASC')
            ->get()
            ->getResultArray();

        return $this->attachAnswers($questions);
    }

    private function attachAnswers(array $questions): array
    {
        //aggancia le risposte attive usando lo stesso model usato dal percorso normale
        $answerModel = model(AnswerModel::class);
        $filtered = [];
        foreach ($questions as &$question) {
            $question['answers'] = $answerModel->findActiveByQuestion((int) $question['id_question']);
            if (count($question['answers']) === 4) {
                $filtered[] = $question;
            }
        }
        unset($question);

        return $filtered;
    }

    private function pickMixedQuestions(array $questions, int $limit): array
    {
        //distribuisce le domande tra moduli invece di prendere solo i primi moduli
        $byModule = [];
        foreach ($questions as $question) {
            $byModule[(int) $question['id_module']][] = $question;
        }

        $mixed = [];
        //gira tra i moduli prendendo una domanda per modulo finché non raggiunge il limite o finisce le domande
        while (count($mixed) < $limit && !empty($byModule)) {
            foreach (array_keys($byModule) as $moduleId) {
                if (empty($byModule[$moduleId])) {
                    unset($byModule[$moduleId]);
                    continue;
                }

                $mixed[] = array_shift($byModule[$moduleId]);
                if (count($mixed) >= $limit) {
                    break;
                }
            }
        }

        return $mixed;
    }
}
