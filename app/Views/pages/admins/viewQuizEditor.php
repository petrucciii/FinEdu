<div id="content-wrapper">
    <?php //barra superiore dell'editor quiz ?>
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Quiz Editor</h5>
        <div class="d-flex gap-2">
            <a href="/admin/QuizManagementController/" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Elenco quiz
            </a>
            <a href="/admin/ModuleManagementController/" class="btn btn-outline-primary">
                <i class="fas fa-graduation-cap me-1"></i> Moduli
            </a>
        </div>
    </div>

    <div class="main-content pt-0">
        <?php //intestazione con modulo e lesson corrente ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="text-muted text-uppercase mb-0">Quiz Editor</h6>
                <h3><?= esc($lesson['title']) ?></h3>
                <small class="text-muted">
                    <?= esc($lesson['module_name']) ?> &gt; Lezione #<?= (int) $lesson['id_lesson'] ?>
                </small>
            </div>
        </div>

        <?php //form che modifica la domanda salvata nei campi della lesson ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Domanda del quiz</h5>
            </div>
            <div class="card-body">
                <form action="/admin/QuizManagementController/updatePrompt" method="post">
                    <input type="hidden" name="id_lesson" value="<?= (int) $lesson['id_lesson'] ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Titolo lezione</label>
                            <input type="text" name="title" class="form-control" maxlength="50" required
                                value="<?= esc($lesson['title'], 'attr') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Domanda</label>
                            <input type="text" name="description" class="form-control" maxlength="255" required
                                value="<?= esc($lesson['description'], 'attr') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hint errore</label>
                            <input type="text" name="hint" class="form-control" maxlength="255"
                                value="<?= esc($lesson['hint'], 'attr') ?>">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fas fa-save me-1"></i> Salva domanda
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php //un quiz puo avere un solo set di quattro risposte ?>
        <div class="card card-dashboard">
            <div class="card-header bg-white">
                <h5 class="mb-0">Set di 4 risposte</h5>
            </div>
            <div class="card-body">
                <?php if (empty($question)): ?>
                    <?php //caso di recupero dati: crea l'unico set se manca ?>
                    <div class="alert alert-warning border-0 shadow-sm">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Questo quiz non ha ancora il set di 4 risposte.
                    </div>
                    <form action="/admin/QuizManagementController/createQuestion" method="post">
                        <input type="hidden" name="id_lesson" value="<?= (int) $lesson['id_lesson'] ?>">
                        <div class="mb-3">
                            <label class="fw-bold">Punti esperienza</label>
                            <input type="number" name="experience" class="form-control w-25" min="0" value="0">
                        </div>
                        <div class="row">
                            <?php //quattro input obbligatori per il set unico ?>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <input class="form-check-input mt-0" type="radio" name="correct_index"
                                                value="<?= (int) $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                        </span>
                                        <input type="text" name="answer_text[<?= (int) $i ?>]" class="form-control"
                                            maxlength="255" required placeholder="Risposta <?= (int) ($i + 1) ?>">
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Salva risposte</button>
                        </div>
                    </form>
                <?php else: ?>
                    <?php //modifica dell'unico set risposte gia presente ?>
                    <form action="/admin/QuizManagementController/updateQuestion" method="post">
                        <input type="hidden" name="id_question" value="<?= (int) $question['id_question'] ?>">
                        <div class="mb-3">
                            <label class="fw-bold">Punti esperienza</label>
                            <input type="number" name="experience" class="form-control w-25" min="0"
                                value="<?= (int) $question['experience'] ?>">
                        </div>
                        <div class="row">
                            <?php
                            //mantiene sempre quattro righe per rispettare il vincolo del quiz
                            $answers = $question['answers'] ?? [];
                            ?>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <?php
                                $answer = $answers[$i] ?? null;
                                $isCorrect = $answer ? (int) $answer['is_correct'] === 1 : ($i === 0 && empty($answers));
                                ?>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <input class="form-check-input mt-0" type="radio" name="correct_index"
                                                value="<?= (int) $i ?>" <?= $isCorrect ? 'checked' : '' ?>>
                                        </span>
                                        <input type="text" name="answer_text[<?= (int) $i ?>]" class="form-control"
                                            maxlength="255" required value="<?= esc($answer['answer'] ?? '', 'attr') ?>"
                                            placeholder="Risposta <?= (int) ($i + 1) ?>">
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="fas fa-save me-1"></i> Aggiorna risposte
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
