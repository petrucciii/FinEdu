<div id="content-wrapper">
    <?php //barra superiore della lista quiz ?>
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Gestione Quiz</h5>
        <a href="/admin/ModuleManagementController/" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Moduli
        </a>
    </div>

    <div class="main-content pt-0">
        <?php //tabella riepilogo dei quiz ricavati dalle lezioni con questions ?>
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Quiz configurati</h5>
                    <p class="text-muted small mb-0">Ogni quiz e una lezione con un solo set di 4 risposte.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Modulo</th>
                                <th>Lezione quiz</th>
                                <th>Domanda</th>
                                <th>XP</th>
                                <th>Set risposte</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php //stato vuoto se nessuna lezione e specializzata come quiz ?>
                            <?php if (empty($quizzes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Nessun quiz attivo. Crea una lezione di tipo quiz nella gestione moduli.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php //righe dei quiz attivi con collegamento all'editor risposte ?>
                            <?php foreach ($quizzes as $quiz): ?>
                                <tr>
                                    <td><?= esc($quiz['module_name']) ?></td>
                                    <td class="fw-bold"><?= esc($quiz['title']) ?></td>
                                    <td><?= esc($quiz['description']) ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i><?= (int) $quiz['experience'] ?>
                                        </span>
                                    </td>
                                    <td><?= (int) $quiz['question_count'] ?></td>
                                    <td class="text-end">
                                        <a href="/admin/QuizManagementController/editor/<?= (int) $quiz['id_lesson'] ?>"
                                            class="btn btn-sm btn-light text-warning border shadow-sm">
                                            <i class="fas fa-tasks"></i> Editor
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
