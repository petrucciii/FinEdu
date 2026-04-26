<div id="content-wrapper" data-selected-user-id="<?= !empty($selectedUserId) ? (int) $selectedUserId : '' ?>">
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Progressi Educazione</h5>
        <a href="/admin/ModuleManagementController/" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Moduli
        </a>
    </div>

    <div class="main-content pt-0">
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">Progressi utenti</h5>
                    <p class="text-muted small mb-0">Vista in sola lettura basata su completed_lessons e users.experience.</p>
                </div>

                <?php
                /*
                 * Input unico per la ricerca dinamica.
                 * Non è più un form con submit: come nella gestione news, il JS ascolta
                 * l'evento input e chiede al controller i risultati filtrati via AJAX.
                 */
                ?>
                <div class="input-group input-group-sm" style="max-width: 320px;">
                    <input type="text" id="progressSearchInput" class="form-control" placeholder="Cerca nome, cognome o email..."
                        value="<?= esc($search ?? '', 'attr') ?>">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Utente</th>
                                <th>Email</th>
                                <th>Livello</th>
                                <th>XP</th>
                                <th>Lezioni completate</th>
                                <th>Tentativi</th>
                                <th>Errori</th>
                                <th>Ultima attività</th>
                            </tr>
                        </thead>
                        <tbody id="educationProgressBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center" id="paginationContainer"></div>
            </div>
        </div>
    </div>

    <?php
    /*
     * Template riga usato da educationProgress.js.
     * Tenere l'HTML nella view e i dati nel JS evita concatenazioni lunghe e rende più
     * chiaro quali celle vengono riempite dai risultati JSON.
     */
    ?>
    <template id="educationProgressRowTemplate">
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div data-field="avatar"
                        class="text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                        style="width:38px; height:38px; font-weight:bold;"></div>
                    <strong data-field="full_name"></strong>
                </div>
            </td>
            <td data-field="email"></td>
            <td><span class="badge bg-info text-dark" data-field="level"></span></td>
            <td><span class="fw-bold text-primary" data-field="experience"></span></td>
            <td style="min-width: 190px;">
                <div class="d-flex justify-content-between small mb-1">
                    <span data-field="completed_text"></span>
                    <span class="fw-bold" data-field="percent_text"></span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" data-field="progress_bar" style="width: 0%"></div>
                </div>
            </td>
            <td data-field="total_attempts"></td>
            <td data-field="failed_attempts"></td>
            <td class="small text-muted" data-field="last_activity"></td>
        </tr>
    </template>

    <script type="module" src="<?= base_url('javascript/ajax/educationProgress.js') ?>"></script>
</div>
