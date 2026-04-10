<div id="content-wrapper" class="container-fluid container-xl mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark">Gestione Portafogli</h4>
    </div>

    <div class="card card-dashboard bg-white border-0 shadow-sm">
        <div
            class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 text-muted">Tutti i portafogli attivi</h6>
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Cerca portafoglio o utente...">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <!-- header con data-order per ordinamento da AJAX -->
                            <th><a data-order="portfolios.portfolio_id">ID <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="users.email">Utente <i class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="portfolios.name">Nome <i class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="portfolios.inital_liquidity">Liq. iniziale <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="portfolios.liquidity">Liq. attuale <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="portfolios.invested">Investito <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="calculated_mv">Valore titoli <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th><a data-order="calculated_total">Totale <i class="fas fa-sort-amount-up ms-1"></i></a>
                            </th>
                            <th><a data-order="calculated_pnl">P&amp;L non real. <i
                                        class="fas fa-sort-amount-up ms-1"></i></a></th>
                            <th class="text-end">Ordini</th>
                        </tr>
                    </thead>
                    <!-- reimpita con ajax -->
                    <tbody id="portfoliosTableBody"></tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
            </div>
        </div>
    </div>
    <!-- come in altre view, elemento speciale non letto da DOM. (viewUserManagement, viewCompanyList, viewNewsManagement) -->
    <template id="portfolioRowTemplate">
        <tr>
            <td class="text-muted" data-field="pid"></td>
            <td data-field="user_cell"></td>
            <td class="fw-bold" data-field="pname"></td>
            <td data-field="init_liq"></td>
            <td data-field="liq"></td>
            <td data-field="inv"></td>
            <td data-field="mv"></td>
            <td class="fw-semibold" data-field="total"></td>
            <td data-field="unreal"></td>
            <td class="text-end">
                <a href="/admin/OrderManagementController/" class="btn btn-sm btn-light text-primary border shadow-sm"
                    data-field="orders_link"><i class="fas fa-list-ul"></i> Ordini</a>
            </td>
        </tr>
    </template>

    <script type="module" src="<?= base_url('javascript/ajax/portfolioManagement.js') ?>"></script>
</div>