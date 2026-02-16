<div id="content-wrapper">
    <div class="top-bar d-flex justify-content-between align-items-center">
        <h5 class="m-0 text-muted">Panoramica</h5>
    </div>
    <div class="main-content">
        <h2 class="mb-4">Dashboard Amministrativa</h2>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-dashboard bg-primary text-white p-4">
                    <h3 class="mb-2">
                        <?= $userCount ?>
                    </h3>
                    <span>Utenti Registrati</span>
                    <!-- <small class="mt-2 opacity-75"><i class="fas fa-arrow-up"></i></small> -->
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-success text-white p-4">
                    <h3 class="mb-2">45</h3>
                    <span>Società Quotate</span>
                    <!-- <small class="mt-2 opacity-75"><i class="fas fa-arrow-up"></i> +3 nuove</small> -->
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-warning text-dark p-4">
                    <h3 class="mb-2">12</h3>
                    <span>Moduli Educativi</span>
                    <!-- <small class="mt-2 opacity-75"><i class="fas fa-check"></i> Tutti attivi</small> -->
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-info text-white p-4">
                    <h3 class="mb-2">328</h3>
                    <span>Ordini Oggi</span>
                    <!-- <small class="mt-2 opacity-75"><i class="fas fa-chart-line"></i> Trend positivo</small> -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card card-dashboard">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Attività Recenti</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Nuova Lezione Completata</strong>
                                    <div class="small text-muted">Lezione 3, Modulo 1 - mario.rossi@libero.it</div>
                                </div>
                                <small class="text-muted">5 min fa</small>
                            </div>
                            <div class="list-group-item d-flex align-items-center">
                                <div class="bg-warning text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Ordine completato</strong>
                                    <div class="small text-muted">Ferrari N.V. EUR 308,4 - mario.rossi@libero.it</div>
                                </div>
                                <small class="text-muted">1 ora fa</small>
                            </div>

                            <div class="list-group-item d-flex align-items-center">
                                <div class="bg-info text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Notizia pubblicata</strong>
                                    <div class="small text-muted">BCE alza i tassi di interesse</div>
                                </div>
                                <small class="text-muted">3 ore fa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-dashboard mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Accesso Rapido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/admin/CompanyManagementController/" class="btn btn-outline-primary"><i
                                    class="fas fa-building me-2"></i> Aggiungi Società</a>
                            <a href="/admin/NewsManagementController/" class="btn btn-outline-success"><i
                                    class="fas fa-newspaper me-2"></i> Pubblica News</a>
                            <a href="/admin/EducationManagementController/" class="btn btn-outline-info"><i
                                    class="fas fa-graduation-cap me-2"></i> Crea Modulo</a>
                            <a href="/admin/UserManagementController/" class="btn btn-outline-warning"><i
                                    class="fas fa-users me-2"></i>
                                Gestisci Utenti</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>