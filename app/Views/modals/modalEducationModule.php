<?php //modal per creare un nuovo modulo educativo ?>
<div class="modal fade" id="newModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Crea nuovo modulo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/ModuleManagementController/createModule" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nome modulo</label>
                        <input type="text" name="name" class="form-control" maxlength="50" required
                            placeholder="Es. Diversificazione">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Descrizione</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="200" required
                            placeholder="Inserisci una breve descrizione..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary fw-bold">Salva modulo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php //modal popolato via javascript per modificare un modulo esistente ?>
<div class="modal fade" id="editModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-edit text-primary me-2"></i>Modifica modulo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/ModuleManagementController/updateModule" method="post">
                <input type="hidden" name="id_module" id="edit_module_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nome modulo</label>
                        <input type="text" name="name" id="edit_module_name" class="form-control" maxlength="50"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Descrizione</label>
                        <textarea name="description" id="edit_module_description" class="form-control" rows="3"
                            maxlength="200" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success fw-bold">Aggiorna modulo</button>
                </div>
            </form>
        </div>
    </div>
</div>
