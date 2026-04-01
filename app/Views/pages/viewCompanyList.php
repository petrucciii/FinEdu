<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted"><?= $adminSection ? "Gestione Società" : "Lista Società" ?></h5>
        <div class="d-flex gap-2">
            <div class="d-flex justify-content-between align-items-center mb-4">

                <?php if ($adminSection): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                        <i class="fas fa-plus"></i> Nuova Società
                    </button>
                <?php endif; ?>
            </div>
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
            <td>
                <div class="d-flex align-items-center">
                    <img src="" data-field="logo" class="rounded me-2 border" width="40" height="40"
                        style="object-fit: contain;">
                    <strong data-field="name"></strong>
                </div>
            </td>
            <td data-field="isin"></td>
            <td data-field="sector"></td>
            <td data-field="country"></td>
            <td></td>
            <td class="text-end">
                <?php if ($adminSection): ?>
                    <a href="" data-field="edit_btn" class="btn btn-sm btn-light text-primary border shadow-sm"
                        title="Modifica">
                        <i class="fas fa-edit"></i>
                    </a>
                <?php else: ?>
                    <a href="" data-field="view_btn" class="btn btn-sm btn-primary">
                        Vedi
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    </template>

    <!-- <?/*= $this->include("modals/modalOrderAdd"); */ ?> -->
    <?= $this->include("modals/modalCompanyAdd"); ?>
</div>

<script type="module" src="<?= base_url('javascript/ajax/companyList.js') ?>"></script>