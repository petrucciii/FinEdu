<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Esplora Mercati</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" type="button" id="refreshMarketBtn">
                <i class="fas fa-sync-alt"></i> Aggiorna Dati
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card card-dashboard">

                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista Società Quotate</h5>
                    <div class="d-flex gap-2 filter-container">

                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Cerca società, ticker o ISIN...">
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-industry"></i> Filtra per Settore
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-sector_id="all">Tutti i settori</button></li>
                                <?php foreach ($sectors as $sector): ?>
                                    <li>
                                        <button class="dropdown-item" data-sector_id="<?= $sector['sector'] ?>">
                                            <?= htmlspecialchars($sector['sector']) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-globe"></i> Filtra per Borsa
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-mic="all">Tutte le Borse</button></li>
                                <?php foreach ($exchanges as $exchange): ?>
                                    <li>
                                        <button class="dropdown-item" data-mic="<?= $exchange['mic'] ?>">
                                            <span class="badge bg-dark me-2">
                                                <?= $exchange['mic'] ?>
                                            </span>
                                            <?= htmlspecialchars($exchange['short_name']) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover w-100" id="companiesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Logo</th>
                                    <th><a data-order="name">Società <i class="fas fa-sort-amount-up ms-1"></i></a></th>
                                    <th><a data-order="isin">ISIN / Ticker <i
                                                class="fas fa-sort-amount-up ms-1"></i></a></th>
                                    <th><a data-order="sector">Settore <i class="fas fa-sort-amount-up ms-1"></i></a>
                                    </th>
                                    <th>Borsa (MIC)</th>
                                    <th class="text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="companiesTableBody"></tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <template id="companyRowTemplate">
        <tr>
            <td>
                <img data-field="logo" src="" alt="Logo" class="rounded border bg-white" width="40" height="40"
                    style="object-fit: contain;">
            </td>

            <td>
                <strong data-field="name" class="d-block text-dark"></strong>
                <small data-field="country" class="text-muted"></small>
            </td>

            <td>
                <span data-field="isin" class="text-muted font-monospace d-block small"></span>
                <span data-field="ticker" class="badge bg-secondary border shadow-sm"></span>
            </td>

            <td>
                <span class="badge bg-info text-dark bg-opacity-25 border border-info" data-field="sector"></span>
            </td>

            <td>
                <span class="badge bg-dark" data-field="mic"></span>
                <small data-field="currency" class="text-muted ms-1 fw-bold"></small>
            </td>

            <td class="text-end">
                <button class="btn btn-sm btn-success shadow-sm open-order-btn fw-bold" data-field="order_btn">
                    <i class="fas fa-shopping-cart"></i> Negozia
                </button>
            </td>
        </tr>
    </template>

    <?= $this->include("modals/modalOrderAdd"); ?>
    <script type="module" src="<?= base_url('javascript/ajaxMarketCompanies.js') ?>"></script>

</div>