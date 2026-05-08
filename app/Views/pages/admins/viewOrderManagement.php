<div id="content-wrapper" class="container-fluid container-xl mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark">Gestione Ordini</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <small class="text-muted fw-bold">Titoli pi&ugrave; acquistati (posizioni aperte)</small>
                </div>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($topCompanies as $tc): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= esc($tc['name'] ?? $tc['isin']) ?></span>
                            <span class="badge bg-primary"><?= (int) $tc['total_qty'] ?> q.t&agrave;</span>
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
                <div class="card-header bg-white py-2">
                    <small class="text-muted fw-bold">Utenti per P&amp;L realizzato (netto)</small>
                </div>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($topUsers as $tu): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= esc($tu['first_name'] . ' ' . $tu['last_name']) ?></span>
                            <span class="fw-bold <?= $tu['total_pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                &euro; <?= number_format((float) $tu['total_pnl'], 2, ',', '.') ?>
                            </span>
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
                <div class="card-header bg-white py-2">
                    <small class="text-muted fw-bold">Migliori trade chiusi (netto)</small>
                </div>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($bestTrades as $bt): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>#<?= (int) $bt['order_id'] ?> <?= esc($bt['ticker']) ?></span>
                                <span class="text-success fw-bold">
                                    &euro; <?= number_format((float) ($bt['trade_pnl'] ?? 0), 2, ',', '.') ?>
                                </span>
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

    <div class="card card-dashboard bg-white border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom-0">
            <div id="ordersFilterAlert" class="alert alert-warning py-2 d-none"></div>
            <div class="row g-2 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label small mb-1">Ricerca</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cerca ticker, utente...">
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="row g-2 justify-content-lg-end">
                        <div class="col-md-4 col-xl-2">
                            <label class="form-label small mb-1">Utente</label>
                            <select id="filterUser" class="form-select form-select-sm">
                                <option value="all">Tutti gli utenti</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= (int) $u['user_id'] ?>">
                                        <?= esc($u['first_name'] . ' ' . $u['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-xl-2">
                            <label class="form-label small mb-1">Portafoglio</label>
                            <select id="filterPortfolio" class="form-select form-select-sm">
                                <option value="all">Tutti i portafogli</option>
                                <?php foreach ($portfolios as $p): ?>
                                    <option value="<?= (int) $p['portfolio_id'] ?>">
                                        <?= esc($p['name']) ?> (#<?= (int) $p['portfolio_id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-xl-2">
                            <label class="form-label small mb-1">MIC</label>
                            <select id="filterMic" class="form-select form-select-sm">
                                <option value="">Tutti i MIC</option>
                                <?php foreach ($exchanges as $ex): ?>
                                    <option value="<?= esc($ex['mic']) ?>"><?= esc($ex['mic']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 col-xl-1">
                            <label class="form-label small mb-1">Ticker</label>
                            <input type="text" id="filterTicker" class="form-control form-control-sm" placeholder="Ticker">
                        </div>

                        <div class="col-md-3 col-xl-1">
                            <label class="form-label small mb-1">Stato</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="all">Tutti</option>
                                <option value="1">Aperti</option>
                                <option value="0">Chiusi</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-xl-2">
                            <label class="form-label small mb-1">Data inizio ordine</label>
                            <input type="date" id="filterDateStart" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-3 col-xl-2">
                            <label class="form-label small mb-1">Data fine ordine</label>
                            <input type="date" id="filterDateEnd" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-3 col-xl-1">
                            <label class="form-label small mb-1">P&amp;L min</label>
                            <input type="number" step="0.01" id="filterPnlMin" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-3 col-xl-1">
                            <label class="form-label small mb-1">P&amp;L max</label>
                            <input type="number" step="0.01" id="filterPnlMax" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="order_id">ID</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="date">Data apertura</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="closed_at">Data chiusura</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="user">Utente</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="portfolio">Portafoglio</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="ticker">Ticker (MIC)</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="quantity">Q.t&agrave;</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="buyPrice">Prezzo acq.</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="sellPrice">Prezzo vend.</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="notional">Controvalore</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="realized_pnl">P&amp;L netto</button></th>
                            <th><button type="button" class="btn btn-link p-0 text-dark order-sort" data-sort="status">Stato</button></th>
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
