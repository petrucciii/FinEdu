<div class="modal fade" id="modalAggiungiNews" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="card card-dashboard w-100 border-0 shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="m-0">Pubblica Notizia</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="card-body bg-white">
                    <form action="/admin/NewsManagementController/create" method="post">
                        <div class="mb-3">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="headline" class="form-control" required
                                placeholder="Es: Trimestrale oltre le attese">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sottotitolo</label>
                            <input type="text" name="subtitle" class="form-control" required
                                placeholder="Breve descrizione della notizia">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fonte</label>
                            <select name="newspaper_id" class="form-select" required>
                                <?php foreach ($newspapers as $np): ?>
                                    <option value="<?= (int) $np['newspaper_id'] ?>"><?= esc($np['newspaper']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Autore</label>
                            <input type="text" name="author" class="form-control" required placeholder="Nome autore">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contenuto</label>
                            <textarea name="body" class="form-control" rows="6" required
                                placeholder="Corpo della notizia..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Società collegata (opzionale)</label>
                            <select name="isin1" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seconda società (opzionale)</label>
                            <select name="isin2" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terza società (opzionale)</label>
                            <select name="isin3" class="form-select">
                                <option value="">— Nessuna —</option>
                                <?php foreach ($companies as $c): ?>
                                    <option value="<?= esc($c['isin']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i>
                            Pubblica</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
