<div class="container mt-4 mb-5" style="min-height: 80vh;">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <h3 class="fw-bold mb-1">Test iniziale</h3>
                            <p class="text-muted mb-0">
                                Domande miste dai moduli attivi. Il test non penalizza: serve solo a riconoscere
                                eventuali moduli gi&agrave; padroneggiati.
                            </p>
                        </div>
                        <a href="<?= base_url('EducationController/skipInitialTest') ?>" class="btn btn-outline-secondary">
                            Salta
                        </a>
                    </div>
                </div>
            </div>

            <form action="<?= base_url('EducationController/submitInitialTest') ?>" method="post">
                <?php foreach ($questions as $index => $question): ?>
                    <?php $answers = $question['answers'] ?? []; ?>
                    <input type="hidden" name="question_ids[]" value="<?= (int) $question['id_question'] ?>">

                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary">
                                    <?= esc($question['module_name'] ?? 'Modulo') ?>
                                </span>
                                <span class="small text-muted">
                                    <?= (int) ($index + 1) ?> / <?= count($questions) ?>
                                </span>
                            </div>
                            <h5 class="fw-bold"><?= esc($question['title'] ?? 'Domanda') ?></h5>
                            <p class="text-muted"><?= esc($question['description'] ?? '') ?></p>

                            <?php if (count($answers) !== 4): ?>
                                <div class="alert alert-warning border-0 mb-0">
                                    Quiz non configurato correttamente.
                                </div>
                            <?php else: ?>
                                <?php foreach ($answers as $answer): ?>
                                    <?php $inputId = 'initialAnswer' . (int) $answer['id_answer']; ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio"
                                            name="answers[<?= (int) $question['id_question'] ?>]"
                                            value="<?= (int) $answer['id_answer'] ?>" id="<?= esc($inputId) ?>" required>
                                        <label class="form-check-label" for="<?= esc($inputId) ?>">
                                            <?= esc($answer['answer']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex justify-content-end gap-2">
                        <a href="<?= base_url('EducationController/index') ?>" class="btn btn-light">Annulla</a>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="fas fa-check me-2"></i>Concludi test
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
