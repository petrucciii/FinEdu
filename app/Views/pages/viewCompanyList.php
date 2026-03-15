<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Esplora Mercati</h5>
        <div class="d-flex gap-2">

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



                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover w-100" id="companiesTable">
                            <thead class="table-light">
                                <tr>
                                    <th><a data-order="name">Società</a></th>
                                    <th><a data-order="name">Sede Legale</a></th>
                                    <th><a data-order="isin">ISIN</a></th>
                                    <th><a data-order="sector">Settore</a>
                                    <th></th>
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
            <td class="align-middle">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img data-field="logo" src="" alt="logo" class="rounded bg-white shadow-sm p-1"
                            style="width: 40px; height: 40px; object-fit: contain; border-radius: 40px">
                    </div>
                    <!-- Testo con margine a sinistra e font-weight bilanciato -->
                    <div class="ms-3">
                        <span data-field="name" class="fw-bold text-dark d-block lh-sm"></span>
                    </div>
                </div>
            </td>
            <td><small data-field="country" class="text-muted"></small></td>
            <td>
                <span data-field="isin" class="text-muted font-monospace d-block small"></span>
            </td>
            <td>
                <span data-field="sector" class="text-muted"></span>
            </td>

            <td>
                <span class="badge bg-info text-dark bg-opacity-25 border border-info" data-field="sector"></span>
            </td>


            <td class="text-end">
                <button class="btn btn-sm btn-primary shadow-sm open-order-btn fw-bold" data-field="view_btn">
                    <i class="fas fa-eye"></i> Visualizza
                </button>
            </td>
        </tr>
    </template>

    <?/*= $this->include("modals/modalOrderAdd"); */ ?>
    <script type="module" src="<?= base_url('javascript/ajax/companyList.js') ?>"></script>

</div>