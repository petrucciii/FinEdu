<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Gestione Utenti</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" type="button">
                <i class="fas fa-download"></i> Esporta CSV
            </button>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Aggiungi Utente
            </button>
        </div>
    </div>


    <div class="main-content">
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card card-dashboard">

                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Database Utenti</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Cerca email o nome...">
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filtra
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Tutti gli utenti</a></li>
                                <li><a class="dropdown-item" href="#">Livello: Principiante</a></li>
                                <li><a class="dropdown-item" href="#">Livello: Intermedio</a></li>
                                <li><a class="dropdown-item" href="#">Livello: Avanzato</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover w-100" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Utente</th>
                                    <th>Email</th>
                                    <th>Ruolo</th>
                                    <th>Livello Educazione</th>
                                    <th>Portafogli</th>
                                    <th>Data Registrazione</th>
                                    <th class="text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody"></tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!--special tag, the DOM doesn't render it. used to clone rows in JS. later will be inserted into tbody -->
    <template id="userRowTemplate">
        <tr>
            <td class="text-muted" data-field="user_id"></td>

            <td>
                <div class="d-flex align-items-center">
                    <div data-field="avatar"
                        class="text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                        style="width:38px; height:38px; font-weight:bold;"></div>
                    <strong data-field="full_name"></strong>
                </div>
            </td>

            <td data-field="email"></td>

            <td>
                <span class="badge" data-field="role"></span>
            </td>

            <td>
                <span class="badge" data-field="level"></span>
            </td>

            <td>
                <span class="fw-bold">0</span>
            </td>

            <td class="small text-muted" data-field="created_at"></td>

            <td class="text-end">
                <button class="btn btn-sm btn-light border text-primary open-user-btn" data-field="manage_btn">
                    <i class="fas fa-cog"></i> Gestisci
                </button>
            </td>
        </tr>
    </template>


    <?= $this->include("modals/modalUserManagement"); ?>
    <script src="<?= base_url('javascript/ajaxUserManagement.js') ?>"></script>

</div>