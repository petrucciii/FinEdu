<div id="content-wrapper" class="container-fluid container-xl mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark">Gestione Ordini</h4>
    </div>

    <!-- DASHBOARD CON STATS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">

            <div class="card card-dashboard border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><small class="text-muted fw-bold">Titoli più acquistati
                        (posizioni
                        aperte)</small></div>
                <ul class="list-group list-group-flush small">
                    <!-- lista delle società più acquistate -->
                    <?php foreach ($topCompanies as $tc): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= esc($tc['name'] ?? $tc['isin']) ?></span>
                            <span class="badge bg-primary"><?= (int) $tc['total_qty'] ?> q.tà</span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($topCompanies)): ?>
                        <li class="list-group-item text-muted">Nessun dato</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-dashboard border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><small class="text-muted fw-bold">Utenti per P&amp;L realizzato
                        (netto)</small></div>
                <ul class="list-group list-group-flush small">
                    <!-- utenti piu profittevoli -->
                    <?php foreach ($topUsers as $tu): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= esc($tu['first_name'] . ' ' . $tu['last_name']) ?></span>
                            <span class="fw-bold <?= $tu['total_pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">€
                                <?= number_format((float) $tu['total_pnl'], 2, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($topUsers)): ?>
                        <li class="list-group-item text-muted">Nessun dato</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><small class="text-muted fw-bold">Migliori trade chiusi
                        (netto)</small></div>
                <ul class="list-group list-group-flush small">
                    <!-- migliori ordini chiusi -->
                    <?php foreach ($bestTrades as $bt): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>#<?= (int) $bt['order_id'] ?>     <?= esc($bt['ticker']) ?></span>
                                <span class="text-success fw-bold">€
                                    <?= number_format((float) ($bt['trade_pnl'] ?? 0), 2, ',', '.') ?></span>
                            </div>
                            <div class="text-muted"><?= esc($bt['first_name'] . ' ' . $bt['last_name']) ?></div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($bestTrades)): ?>
                        <li class="list-group-item text-muted">Nessun dato</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- LISTA ORDINI -->
    <div class="card card-dashboard bg-white border-0 shadow-sm">
        <!-- filtri -->
        <div class="card-header bg-white py-3 border-bottom-0">
            <div class="row g-2 align-items-center">
                <div class="col-lg-3">
                    <!-- ricerca -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cerca ticker, utente...">
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        <select id="filterUser" class="form-select form-select-sm"
                            style="width: auto; min-width: 160px;">
                            <!-- per utente -->
                            <option value="all">Tutti gli utenti</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int) $u['user_id'] ?>">
                                    <?= esc($u['first_name'] . ' ' . $u['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- per portafoglio -->
                        <select id="filterPortfolio" class="form-select form-select-sm"
                            style="width: auto; min-width: 160px;">
                            <option value="all">Tutti i portafogli</option>
                            <?php foreach ($portfolios as $p): ?>
                                <option value="<?= (int) $p['portfolio_id'] ?>"><?= esc($p['name']) ?>
                                    (#<?= (int) $p['portfolio_id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- per borsa -->
                        <select id="filterMic" class="form-select form-select-sm"
                            style="width: auto; min-width: 140px;">
                            <option value="">Tutti i MIC</option>
                            <?php foreach ($exchanges as $ex): ?>
                                <option value="<?= esc($ex['mic']) ?>"><?= esc($ex['mic']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!-- per ticker -->
                        <input type="text" id="filterTicker" class="form-control form-control-sm" placeholder="Ticker"
                            style="width: 100px;">
                        <!-- per stato -->
                        <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                            <option value="all">Tutti gli stati</option>
                            <option value="1">Aperti</option>
                            <option value="0">Chiusi</option>
                        </select>
                        <!-- per data e P&L -->
                        <input type="date" id="filterDate" class="form-control form-control-sm" title="Da data">
                        <input type="number" step="0.01" id="filterPnlMin" class="form-control form-control-sm"
                            placeholder="P&amp;L min" style="width: 110px;">
                        <input type="number" step="0.01" id="filterPnlMax" class="form-control form-control-sm"
                            placeholder="P&amp;L max" style="width: 110px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- tabella fillata da ajax come in viewUserManagement, viewNewsManagement, viewCompanyList -->
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Data apertura</th>
                            <th>Data chiusura</th>
                            <th>Utente</th>
                            <th>Portafoglio</th>
                            <th>Ticker (MIC)</th>
                            <th>Q.tà</th>
                            <th>Prezzo acq.</th>
                            <th>Prezzo vend.</th>
                            <th>Controvalore</th>
                            <th>P&amp;L netto</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody"></tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
            </div>
        </div>
    </div>

    <!-- non letto da DOM -->
    <template id="orderRowTemplate">
        <tr>
            <td class="text-muted fw-bold" data-field="order_id"></td>
            <td class="small" data-field="date"></td>
            <td class="small text-muted" data-field="closed_at"></td>
            <td data-field="user"></td>
            <td data-field="portfolio"></td>
            <td data-field="ticker_mic"></td>
            <td class="fw-bold text-primary" data-field="qty"></td>
            <td data-field="buy"></td>
            <td data-field="sell"></td>
            <td data-field="notional"></td>
            <td data-field="pnl"></td>
            <td data-field="status"></td>
        </tr>
    </template>

    <script type="module" src="<?= base_url('javascript/ajax/orderManagement.js') ?>"></script>
</div>