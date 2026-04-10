<!-- sezione amministrativa news -->
<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Gestione News</h5>
    </div>

    <div class="main-content">
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Ultime Notizie</h5>
                <div class="d-flex flex-grow-1 gap-2 justify-content-end" style="min-width: 280px;">
                    <div class="input-group" style="max-width: 360px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cerca notizia...">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <button class="btn btn-primary text-nowrap" type="button" data-bs-toggle="modal"
                        data-bs-target="#modalAggiungiNews">
                        <i class="fas fa-plus"></i> Aggiungi
                    </button>
                </div>
            </div>

            <!-- scheletro tabella -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:12%">Data</th>
                            <th>Titolo</th>
                            <th style="width:12%">Società</th>
                            <th style="width:15%">Fonte</th>
                            <th style="width:10%" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <!-- contenuto tabella caricato con ajax -->
                    <tbody id="newsTableBody"></tbody>
                </table>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
            </div>
        </div>
    </div>

    <!-- elemento speciale non letto dal DOM così da caricare tutto in una volta (da js) e non riga per riga -->
    <template id="newsRowTemplate">
        <tr>
            <td class="small text-muted" data-field="date_cell"></td>
            <td>
                <strong data-field="headline"></strong>
                <div class="small text-muted" data-field="subtitle"></div>
            </td>
            <td data-field="logos"></td>
            <td><span class="badge bg-secondary" data-field="newspaper"></span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary open-news-edit-btn" data-field="edit_btn">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger open-news-delete-btn" data-field="del_btn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    <?= $this->include('modals/modalNewsAdd') ?>
    <?= $this->include('modals/modalNewsEdit') ?>
    <?= $this->include('modals/modalNewsDelete') ?>

    <script type="module" src="<?= base_url('javascript/ajax/newsManagement.js') ?>"></script>
</div>