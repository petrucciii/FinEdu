<div id="content-wrapper">

    <?php //barra superiore della gestione contenuti educativi ?>
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark">
            <i class="fas fa-graduation-cap text-primary me-2"></i>Percorsi Educativi
        </h4>
        <div class="d-flex gap-2">
            <a href="/admin/ModuleManagementController/progress" class="btn btn-outline-primary fw-bold">
                <i class="fas fa-chart-line me-1"></i> Progressi utenti
            </a>
            <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newModuleModal">
                <i class="fas fa-plus me-1"></i> Nuovo modulo
            </button>
        </div>
    </div>

    <div class="main-content pt-0">
        <?php //titolo e descrizione della pagina admin ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0">Gestione moduli e lezioni</h5>
                <p class="text-muted small mb-0">Organizza il materiale didattico usando solo le tabelle reali del DB.</p>
            </div>
        </div>

        <?php //stato vuoto se non sono presenti moduli attivi ?>
        <?php if (empty($modules)): ?>
            <div class="card card-dashboard border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-graduation-cap text-muted mb-3" style="font-size: 2rem;"></i>
                    <h5 class="fw-bold">Nessun modulo attivo</h5>
                    <p class="text-muted mb-0">Crea il primo modulo per iniziare il percorso formativo.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php //accordion dei moduli con lezioni caricate dal controller ?>
        <div class="accordion shadow-sm rounded-4 overflow-hidden" id="educationAccordion">
            <?php foreach ($modules as $index => $module): ?>
                <?php //id collapse unico per ogni modulo ?>
                <?php $collapseId = 'moduleCollapse' . (int) $module['id_module']; ?>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-white text-dark fw-bold <?= $index === 0 ? '' : 'collapsed' ?>"
                            type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId) ?>">
                            <span class="me-auto">
                                Modulo <?= (int) ($index + 1) ?>: <?= esc($module['name']) ?>
                            </span>
                            <span class="badge bg-primary rounded-pill me-3">
                                <?= (int) $module['lesson_count'] ?> lezioni
                            </span>
                        </button>
                    </h2>
                    <div id="<?= esc($collapseId) ?>"
                        class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                        data-bs-parent="#educationAccordion">
                        <div class="accordion-body bg-light p-4">
                            <?php //riquadro con descrizione modulo e pulsanti gestione ?>
                            <div class="row mb-4 align-items-center bg-white p-3 rounded-3 border shadow-sm mx-0">
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-muted mb-1">Descrizione modulo:</h6>
                                    <p class="mb-0 small"><?= esc($module['description']) ?></p>
                                    <small class="text-muted">
                                        <?= (int) $module['explanation_count'] ?> spiegazioni -
                                        <?= (int) $module['quiz_count'] ?> quiz
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-2 open-module-edit"
                                        data-bs-toggle="modal" data-bs-target="#editModuleModal"
                                        data-id="<?= (int) $module['id_module'] ?>"
                                        data-name="<?= esc($module['name'], 'attr') ?>"
                                        data-description="<?= esc($module['description'], 'attr') ?>">
                                        <i class="fas fa-edit"></i> Modifica
                                    </button>
                                    <button class="btn btn-sm btn-primary open-lesson-add" data-bs-toggle="modal"
                                        data-bs-target="#addLessonModal" data-module-id="<?= (int) $module['id_module'] ?>"
                                        data-module-name="<?= esc($module['name'], 'attr') ?>">
                                        <i class="fas fa-plus"></i> Nuova lezione
                                    </button>
                                    <form action="/admin/ModuleManagementController/deleteModule" method="post"
                                        class="d-inline confirm-submit" data-confirm="Disattivare questo modulo?">
                                        <input type="hidden" name="id_module" value="<?= (int) $module['id_module'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light text-danger border shadow-sm mt-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php //tabella lezioni del modulo con tipo e azioni disponibili ?>
                            <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th>Titolo lezione</th>
                                            <th>Tipo</th>
                                            <th>Info aggiuntive</th>
                                            <th class="text-end">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php //riga vuota se il modulo non contiene lezioni attive ?>
                                        <?php if (empty($module['lessons'])): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Nessuna lezione attiva in questo modulo.
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php //lezioni comuni con specializzazione ricavata dal model ?>
                                        <?php foreach ($module['lessons'] as $lessonIndex => $lesson): ?>
                                            <tr>
                                                <td class="text-center text-muted fw-bold"><?= (int) ($lessonIndex + 1) ?></td>
                                                <td class="fw-bold"><?= esc($lesson['title']) ?></td>
                                                <td>
                                                    <?php //badge del tipo lezione: spiegazione o quiz ?>
                                                    <?php if ($lesson['lesson_type'] === 'explanation'): ?>
                                                        <span class="badge bg-info text-dark">
                                                            <i class="fas fa-book-open me-1"></i> Spiegazione
                                                        </span>
                                                    <?php elseif ($lesson['lesson_type'] === 'quiz'): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-question-circle me-1"></i> Quiz
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-exclamation-triangle me-1"></i> Non configurata
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php //info sintetica diversa tra quiz e spiegazione ?>
                                                    <?php if ($lesson['lesson_type'] === 'quiz'): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-star text-warning"></i>
                                                            <?= (int) $lesson['experience'] ?> XP -
                                                            <?= (int) $lesson['question_count'] ?> set risposte
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted"><?= esc($lesson['hint']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <?php //spiegazioni in modal, quiz direttamente nel Quiz Editor ?>
                                                    <?php if ($lesson['lesson_type'] === 'quiz'): ?>
                                                        <a href="/admin/QuizManagementController/editor/<?= (int) $lesson['id_lesson'] ?>"
                                                            class="btn btn-sm btn-light text-warning border shadow-sm me-1">
                                                            <i class="fas fa-eye"></i> / <i class="fas fa-pen"></i>
                                                            
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-light text-primary border shadow-sm me-1 open-lesson-edit"
                                                            data-bs-toggle="modal" data-bs-target="#editLessonModal"
                                                            data-id="<?= (int) $lesson['id_lesson'] ?>"
                                                            data-title="<?= esc($lesson['title'], 'attr') ?>"
                                                            data-description="<?= esc($lesson['description'], 'attr') ?>"
                                                            data-hint="<?= esc($lesson['hint'], 'attr') ?>"
                                                            data-type="<?= esc($lesson['lesson_type'], 'attr') ?>"
                                                            data-body="<?= esc($lesson['body'] ?? '', 'attr') ?>">
                                                            <i class="fas fa-eye"></i> / <i class="fas fa-pen"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <form action="/admin/ModuleManagementController/deleteLesson" method="post"
                                                        class="d-inline confirm-submit" data-confirm="Disattivare questa lezione?">
                                                        <input type="hidden" name="id_lesson" value="<?= (int) $lesson['id_lesson'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-light text-danger border shadow-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php //modal separati in app/views/modals come nel resto del progetto ?>
    <?= $this->include('modals/modalEducationModule') ?>
    <?= $this->include('modals/modalEducationLesson') ?>
    <script type="module" src="<?= base_url('javascript/educationAdmin.js') ?>"></script>
</div>
