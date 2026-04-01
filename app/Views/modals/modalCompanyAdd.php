<div class="modal fade" id="addCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Registra Nuova Società</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/CompanyManagementController/create') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Codice ISIN</label>
                        <input type="text" name="isin" class="form-control text-uppercase"
                            placeholder="Es. IT0000062072" required maxlength="12">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome Società</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Settore</label>
                            <select name="sector" class="form-select" required>
                                <option value="" disabled selected>Seleziona settore...</option>
                                <?php foreach ($sectors as $sector): ?>
                                    <option value="<?= esc($sector['ea_code']) ?>"><?= esc($sector['description']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Paese</label>
                            <select name="country" class="form-select" required>
                                <option value="" disabled selected>Seleziona paese...</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= esc($country['country_code']) ?>">
                                        <?= esc($country['country']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="small text-muted mb-0"><i class="fas fa-info-circle"></i> Dopo la creazione verrai
                        reindirizzato alla pagina di modifica per inserire bilanci e quotazioni.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Crea Società</button>
                </div>
            </form>
        </div>
    </div>
</div>