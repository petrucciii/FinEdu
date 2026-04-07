<div id="content-wrapper" class="container-fluid container-xl mt-4 mb-5" style="min-height: 80vh;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <!--titolo con icona (lato utente)-->
        <h4 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Quotazioni</h4>
    </div>

    <div class="card card-dashboard bg-white border-0 shadow-sm">
        <!--header con filtri allineati orizzontalmente-->
        <div class="card-header bg-white py-3 border-bottom-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">Titoli quotati disponibili</h6>
                <!--filtri sulla stessa riga con flex-nowrap-->
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cerca ticker, ISIN o società...">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <select id="filterExchange" class="form-select form-select-sm" style="width: 250px;">
                        <option value="">Tutte le borse</option>
                        <?php foreach ($exchanges as $ex): ?>
                            <option value="<?= esc($ex['mic']) ?>"><?= esc($ex['full_name']) ?> (<?= esc($ex['mic']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Ticker</th>
                            <th>ISIN</th>
                            <th>Società</th>
                            <th>Borsa</th>
                            <th>Ultimo Prezzo</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="listingsTableBody"></tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
            </div>
        </div>
    </div>

    <!--template per le righe della tabella, clonato dal js-->
    <template id="listingRowTemplate">
        <tr>
            <td class="fw-bold" data-field="ticker"></td>
            <td class="text-muted small" data-field="isin"></td>
            <td data-field="company_name"></td>
            <td data-field="exchange_name"></td>
            <td data-field="last_price"></td>
            <td class="text-end">
                <button class="btn btn-sm btn-success buy-listing-btn" data-field="buy_btn">
                    <i class="fas fa-shopping-cart"></i> Compra
                </button>
            </td>
        </tr>
    </template>

    <!--modal ordine con stima costo dinamico calcolato dal js-->
    <div class="modal fade" id="buyListingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buyModalTitle">Negozia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/PortfolioController/buy" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="ticker" id="buyTicker">
                        <input type="hidden" name="mic" id="buyMic">
                        <!--prezzo unitario memorizzato dal js per il calcolo costo-->
                        <input type="hidden" id="buyUnitPrice" value="0">
                        <div class="mb-3">
                            <label class="form-label">Portafoglio</label>
                            <select name="portfolio_id" class="form-select" required>
                                <?php foreach ($userPortfolios as $pf): ?>
                                    <option value="<?= (int) $pf['portfolio_id'] ?>">
                                        <?= esc($pf['name']) ?> — Liq. €
                                        <?= number_format((int) $pf['liquidity'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantità</label>
                            <input type="number" name="quantity" id="buyQuantity" class="form-control" min="1" value="1" required>
                        </div>
                        <!--stima costo aggiornata dinamicamente quando si cambia la quantita-->
                        <div class="alert alert-info py-2 mb-0" id="costEstimate">
                            <small>Costo stimato: <strong id="costValue">€ 0,00</strong></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-shopping-cart"></i> Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="module" src="<?= base_url('javascript/ajax/listingSearch.js') ?>"></script>
</div>
