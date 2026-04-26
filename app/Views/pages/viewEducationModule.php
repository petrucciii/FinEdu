<div class="container mt-4 mb-5" style="min-height: 80vh;">
    <div class="row">
        <div class="col-md-9 mx-auto">
            <?php //header del modulo con progress bar aggiornata anche via ajax ?>
            <div class="card p-4 mb-4 border-0 shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-2 gap-3 flex-wrap">
                    <h3 class="mb-0 fw-bold"><?= esc($module['name']) ?></h3>
                    <a href="/EducationController/index" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Moduli
                    </a>
                </div>
                <p class="text-muted mb-0"><?= esc($module['description']) ?></p>
                <div class="mt-3">
                    <div class="mb-2 small">
                        <strong>Progresso complessivo</strong>
                        <span class="float-end fw-bold text-primary" id="moduleProgressPercent">
                            <?= (int) $moduleStatus['progress_percent'] ?>%
                        </span>
                    </div>
                    <div class="progress rounded-pill" style="height: 12px;">
                        <div class="progress-bar bg-primary" id="moduleProgressBar" role="progressbar"
                            style="width: <?= (int) $moduleStatus['progress_percent'] ?>%;"
                            aria-valuenow="<?= (int) $moduleStatus['progress_percent'] ?>" aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted d-block mt-2" id="moduleProgressCount">
                        <?= (int) $moduleStatus['completed_count'] ?> / <?= (int) $moduleStatus['lesson_count'] ?>
                        lezioni completate
                    </small>
                </div>
            </div>

            <?php //messaggio mostrato se il modulo non ha lezioni attive ?>
            <?php if (empty($lessons)): ?>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book-open text-muted mb-3" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold">Nessuna lezione disponibile</h5>
                        <p class="text-muted mb-0">Questo modulo non contiene ancora lezioni attive.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php //accordion delle lezioni nello stesso ordine progressivo del db ?>
            <div class="accordion shadow-sm rounded-4 overflow-hidden" id="moduleAccordion">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <?php
                    //prepara id html e badge in base allo stato della lezione
                    $status = $lesson['status'];
                    $collapseId = 'lessonCollapse' . (int) $lesson['id_lesson'];
                    $headingId = 'lessonHeading' . (int) $lesson['id_lesson'];
                    $isOpen = (int) $openLessonId === (int) $lesson['id_lesson'];
                    $typeIcon = $lesson['lesson_type'] === 'quiz' ? 'fas fa-question-circle text-warning' : 'fas fa-book-open text-primary';
                    $badgeClass = 'bg-secondary';
                    $badgeIcon = 'fas fa-clock';
                    $badgeText = 'Da fare';

                    if ($status === 'completed') {
                        $badgeClass = 'bg-success';
                        $badgeIcon = 'fas fa-check';
                        $badgeText = 'Completata';
                    } elseif ($status === 'locked') {
                        $badgeClass = 'bg-light text-dark border';
                        $badgeIcon = 'fas fa-lock text-muted';
                        $badgeText = 'Bloccata';
                    }
                    ?>
                    <?php //data attribute usati da public/javascript/ajax/education.js per aggiornare la pagina ?>
                    <div class="accordion-item border-0 border-bottom education-lesson-item"
                        data-lesson-id="<?= (int) $lesson['id_lesson'] ?>"
                        data-lesson-type="<?= esc($lesson['lesson_type'], 'attr') ?>"
                        data-status="<?= esc($status, 'attr') ?>">
                        <h2 class="accordion-header" id="<?= esc($headingId) ?>">
                            <button class="accordion-button <?= $isOpen ? '' : 'collapsed' ?> fw-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId) ?>"
                                aria-expanded="<?= $isOpen ? 'true' : 'false' ?>" aria-controls="<?= esc($collapseId) ?>">
                                <i class="<?= esc($typeIcon) ?> me-3"></i>
                                <span class="me-auto"><?= ($index + 1) ?>. <?= esc($lesson['title']) ?></span>
                                <span class="badge <?= esc($badgeClass) ?> me-3 rounded-pill lesson-status-badge">
                                    <i class="<?= esc($badgeIcon) ?> me-1"></i><?= esc($badgeText) ?>
                                </span>
                            </button>
                        </h2>

                        <div id="<?= esc($collapseId) ?>" class="accordion-collapse collapse <?= $isOpen ? 'show' : '' ?>"
                            aria-labelledby="<?= esc($headingId) ?>" data-bs-parent="#moduleAccordion">
                            <div class="accordion-body <?= $lesson['lesson_type'] === 'quiz' ? 'bg-light' : 'bg-white' ?> p-4">
                                <?php //pannello mostrato solo quando la lezione e ancora bloccata ?>
                                <div class="lesson-lock-panel <?= $status === 'locked' ? '' : 'd-none' ?> text-center py-5">
                                    <i class="fas fa-lock text-muted mb-3" style="font-size: 2rem;"></i>
                                    <h6 class="fw-bold text-muted">Lezione bloccata</h6>
                                    <p class="small text-muted mb-0">Completa la lezione precedente per sbloccare questo contenuto.</p>
                                </div>

                                <?php //pannello contenuto mostrato appena la lezione diventa disponibile ?>
                                <div class="lesson-content-panel <?= $status === 'locked' ? 'd-none' : '' ?>">
                                    <?php //contenitore dove il javascript mostra esito immediato del submit ?>
                                    <div class="lesson-feedback"></div>

                                    <?php //ramo spiegazione: mostra body e bottone completamento ?>
                                    <?php if ($lesson['lesson_type'] === 'explanation'): ?>
                                        <h5 class="fw-bold mb-3"><?= esc($lesson['title']) ?></h5>
                                        <p class="text-muted"><?= esc($lesson['description']) ?></p>
                                        <div class="bg-light rounded-3 border p-3 mb-4">
                                            <?= nl2br(esc($lesson['body'] ?? '')) ?>
                                        </div>

                                        <?php if (trim((string) $lesson['hint']) !== ''): ?>
                                            <div class="alert alert-info border-0 shadow-sm">
                                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                                <strong>Ricorda:</strong> <?= esc($lesson['hint']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="text-end mt-4 lesson-result-area">
                                            <?php //area sostituita via ajax quando la spiegazione viene completata ?>
                                            <?php if ($status === 'completed'): ?>
                                                <button class="btn btn-outline-success" disabled>
                                                    <i class="fas fa-check-circle me-1"></i> Lezione completata
                                                </button>
                                            <?php else: ?>
                                                <?php //form intercettato da education.js ma funzionante anche senza javascript ?>
                                                <form action="/EducationController/completeExplanation" method="post"
                                                    class="education-explanation-form" data-lesson-id="<?= (int) $lesson['id_lesson'] ?>">
                                                    <input type="hidden" name="lesson_id" value="<?= (int) $lesson['id_lesson'] ?>">
                                                    <button type="submit" class="btn btn-primary fw-bold px-4">
                                                        <i class="fas fa-check me-2"></i> Completa lezione
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php //ramo quiz: mostra le risposte e invia il tentativo via ajax ?>
                                    <?php elseif ($lesson['lesson_type'] === 'quiz'): ?>
                                        <div class="mb-4">
                                            <h5 class="fw-bold"><i class="fas fa-tasks text-primary me-2"></i>Quiz di verifica</h5>
                                            <p class="text-muted mb-0"><?= esc($lesson['description']) ?></p>
                                            <?php if ((int) $lesson['experience'] > 0): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-star text-warning me-1"></i><?= (int) $lesson['experience'] ?> XP disponibili
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <?php
                                        //controlla lato view che esista l'unico set di quattro risposte
                                        $quizQuestion = $lesson['questions'][0] ?? null;
                                        $quizAnswers = $quizQuestion['answers'] ?? [];
                                        $canSubmit = !$lesson['completed'] && !empty($quizQuestion) && count($quizAnswers) === 4;
                                        ?>

                                        <div class="lesson-result-area">
                                            <?php //area sostituita via ajax quando il quiz viene completato ?>
                                            <?php if ($lesson['completed']): ?>
                                                <div class="alert alert-success border-0 shadow-sm mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>Quiz completato.
                                                </div>
                                            <?php elseif (!$canSubmit): ?>
                                                <div class="alert alert-warning border-0 shadow-sm mb-0">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>Quiz non ancora configurato.
                                                </div>
                                            <?php else: ?>
                                                <?php //form quiz intercettato da education.js per risposta immediata ?>
                                                <form action="/EducationController/submitQuiz" method="post"
                                                    class="education-quiz-form" data-lesson-id="<?= (int) $lesson['id_lesson'] ?>">
                                                    <input type="hidden" name="lesson_id" value="<?= (int) $lesson['id_lesson'] ?>">
                                                    <div class="card border-0 shadow-sm mb-4">
                                                        <div class="card-body">
                                                            <label class="fw-bold mb-3">Scegli la risposta corretta</label>
                                                            <?php foreach ($quizAnswers as $answer): ?>
                                                                <?php $inputId = 'answer' . (int) $answer['id_answer']; ?>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="answers[<?= (int) $quizQuestion['id_question'] ?>]"
                                                                        value="<?= (int) $answer['id_answer'] ?>"
                                                                        id="<?= esc($inputId) ?>" required>
                                                                    <label class="form-check-label" for="<?= esc($inputId) ?>">
                                                                        <?= esc($answer['answer']) ?>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" class="btn btn-primary fw-bold px-4">
                                                            <i class="fas fa-paper-plane me-2"></i>Invia risposta
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning border-0 shadow-sm mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Lezione non configurata come spiegazione o quiz.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script type="module" src="<?= base_url('javascript/ajax/education.js') ?>"></script>
