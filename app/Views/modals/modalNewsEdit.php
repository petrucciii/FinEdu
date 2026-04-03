<!-- modal di modifica news. i campi vengono popolati via ajax da newsManagement.js
     quando l'utente clicca "modifica" su una riga della tabella -->
<div class="modal fade" id="modalModificaNews" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="card card-dashboard w-100 border-0 shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="m-0">Modifica Notizia</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="card-body bg-white">
                    <form action="/admin/NewsManagementController/update" method="post" id="formEditNews">
                        <input type="hidden" name="news_id" id="edit_news_id" value="">
                        <div class="mb-3">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="headline" id="edit_headline" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sottotitolo</label>
                            <input type="text" name="subtitle" id="edit_subtitle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fonte</label>
                            <select name="newspaper_id" id="edit_newspaper_id" class="form-select" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Autore</label>
                            <input type="text" name="author" id="edit_author" class="form-control" required>
                        </div>
                        <!-- corpo della notizia: l'input hidden edit_body contiene l'html
                             che verra inviato al server. quillEditContainer mostra l'editor.
                             il contenuto viene caricato da newsManagement.js tramite
                             quillEdit.root.innerHTML quando si apre il modal -->
                        <div class="mb-3">
                            <label class="form-label">Contenuto</label>
                            <input type="hidden" name="body" id="edit_body" required>
                            <div id="quillEditContainer" class="form-control bg-white"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Società collegata (opzionale)</label>
                            <select name="isin1" id="edit_isin1" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seconda società (opzionale)</label>
                            <select name="isin2" id="edit_isin2" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terza società (opzionale)</label>
                            <select name="isin3" id="edit_isin3" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Salva
                            modifiche</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- carica quill.js che espone la funzione globale initQuillEditor -->
<script src="/javascript/quill.js"></script>
<script>
    //quillEdit deve essere globale (var, non const) perche newsManagement.js
    //ci accede per iniettare il body html quando l'utente apre il modal di modifica
    var quillEdit;

    document.addEventListener('DOMContentLoaded', function () {
        //inizializza quill con validazione sul form 'formEditNews'
        quillEdit = initQuillEditor('quillEditContainer', 'edit_body', 'formEditNews');
    });
</script>