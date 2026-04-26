<?php //modal per creare una lezione dentro un modulo selezionato ?>
<div class="modal fade" id="addLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle me-2"></i>Aggiungi lezione
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/ModuleManagementController/createLesson" method="post" id="addLessonForm">
                <input type="hidden" name="id_module" id="add_lesson_module_id">
                <div class="modal-body p-4">
                    <div class="alert alert-light border small">
                        Modulo: <strong id="add_lesson_module_name"></strong>
                    </div>
                    <div class="row g-3">
                        <?php //campi comuni salvati nella tabella lessons ?>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Titolo lezione</label>
                            <input type="text" name="title" class="form-control" maxlength="50" required
                                placeholder="Es. Introduzione al rischio">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-primary">Tipo di lezione</label>
                            <select class="form-select border-primary" id="addLessonTypeSelect" name="lesson_type"
                                required>
                                <option value="explanation" selected>Spiegazione</option>
                                <option value="quiz">Quiz</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold small">Descrizione / domanda</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="255" required></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold small">Hint</label>
                        <input type="text" name="hint" class="form-control" maxlength="255"
                            placeholder="Suggerimento mostrato all'utente">
                    </div>

                    <hr class="my-4">

                    <?php //campi visibili solo se la lezione e una spiegazione ?>
                    <div id="addFieldsExplanation">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-align-left text-info me-1"></i> Corpo spiegazione
                            </label>
                            <textarea name="body" class="form-control" rows="5" maxlength="255"
                                placeholder="Scrivi qui il contenuto della lezione..."></textarea>
                        </div>
                    </div>

                    <?php //campi visibili solo se la lezione e un quiz ?>
                    <div id="addFieldsQuiz" class="d-none">
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="fas fa-info-circle me-2"></i>
                            Dopo il salvataggio andrai al Quiz Editor per inserire le risposte.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-star text-warning me-1"></i> Punti esperienza
                            </label>
                            <input type="number" name="experience" class="form-control w-50" min="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary fw-bold">Salva lezione</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php //modal di modifica lezione popolato via public/javascript/educationadmin.js ?>
<div class="modal fade" id="editLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-dark border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-pen me-2"></i>Modifica lezione
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/ModuleManagementController/updateLesson" method="post">
                <input type="hidden" name="id_lesson" id="edit_lesson_id">
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <?php //campi comuni della lezione ?>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Titolo lezione</label>
                            <input type="text" name="title" id="edit_lesson_title" class="form-control" maxlength="50"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Tipo</label>
                            <input type="text" id="edit_lesson_type_label" class="form-control bg-white" disabled>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold small">Descrizione / domanda</label>
                        <textarea name="description" id="edit_lesson_description" class="form-control" rows="2"
                            maxlength="255" required></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold small">Hint</label>
                        <input type="text" name="hint" id="edit_lesson_hint" class="form-control" maxlength="255">
                    </div>

                    <?php //body modificabile solo per lezioni spiegazione ?>
                    <div id="editFieldsExplanation" class="mt-3">
                        <label class="form-label fw-bold text-primary">Corpo spiegazione</label>
                        <textarea name="body" id="edit_lesson_body" class="form-control" rows="6"
                            maxlength="255"></textarea>
                    </div>

                    <?php //avviso mostrato quando la lezione e un quiz ?>
                    <div id="editFieldsQuiz" class="alert alert-warning border-0 shadow-sm mt-3 d-none">
                        <i class="fas fa-info-circle me-2"></i>
                        Le risposte e gli XP del quiz si modificano dal Quiz Editor.
                    </div>
                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-save me-2"></i>Salva modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
