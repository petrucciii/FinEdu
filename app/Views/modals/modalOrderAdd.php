<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-shopping-cart me-2"></i>Negozia: <?= esc($company['name']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('OrderController/create') ?>" method="POST">
                <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Borsa</label>
                        <select name="mic" class="form-select" required>
                            <option value="" disabled selected>Seleziona borsa...</option>
                            <?php foreach ($listings as $listing): ?>
                                <option value="<?= esc($listing['mic']) ?>">
                                    <?= esc($listing['ticker']) ?> — <?= esc($listing['mic']) ?>
                                    <?php if (!empty($listing['full_name'])): ?>
                                        (<?= esc($listing['full_name']) ?>)
                                    <?php endif; ?>
                                    <?php if (!empty($listing['currency_code'])): ?>
                                        · <?= esc($listing['currency_code']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($listings)): ?>
                            <small class="text-danger mt-1 d-block">
                                <i class="fas fa-exclamation-triangle"></i> Nessuna quotazione attiva disponibile.
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipo Ordine</label>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="orderTypeBuy" value="BUY" checked>
                                <label class="form-check-label fw-semibold text-success" for="orderTypeBuy">
                                    <i class="fas fa-arrow-up me-1"></i>BUY
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="orderTypeSell" value="SELL">
                                <label class="form-check-label fw-semibold text-danger" for="orderTypeSell">
                                    <i class="fas fa-arrow-down me-1"></i>SELL
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Quantità</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Prezzo (opzionale)</label>
                        <input type="number" name="price" class="form-control" step="0.0001" min="0"
                            placeholder="Lascia vuoto per ordine a mercato">
                        <small class="text-muted">Se non specificato, l'ordine sarà eseguito a mercato.</small>
                    </div>

                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Questa è una piattaforma educativa: gli ordini sono simulati.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success fw-bold" <?= empty($listings) ? 'disabled' : '' ?>>
                        <i class="fas fa-check me-1"></i>Conferma Ordine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
