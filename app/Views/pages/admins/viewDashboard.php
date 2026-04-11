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
                    <h3 class="mb-2"><?= (int) $companyCount ?></h3>
                    <span>Società Quotate</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-warning text-dark p-4">
                    <h3 class="mb-2"><?= (int) $moduleCount ?></h3>
                    <span>Moduli Educativi</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-info text-white p-4">
                    <h3 class="mb-2"><?= (int) $ordersToday ?></h3>
                    <span>Ordini Oggi</span>
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
                        <!-- ordini recenti -->
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentOrders as $ro): ?>
                                <div class="list-group-item d-flex align-items-center">
                                    <div
                                        class="bg-<?= (int) $ro['status'] === 1 ? 'warning' : 'secondary' ?> text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Ordine #<?= (int) $ro['order_id'] ?>
                                            — <?= esc($ro['ticker']) ?></strong>
                                        <div class="small text-muted">
                                            <?= esc($ro['company_name'] ?? '') ?> —
                                            <?= esc(trim(($ro['first_name'] ?? '') . ' ' . ($ro['last_name'] ?? ''))) ?>
                                            — <?= esc($ro['portfolio_name'] ?? '') ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= esc(date('d/m/Y H:i', strtotime($ro['date']))) ?></small>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentOrders)): ?>
                                <div class="list-group-item text-muted small">Nessun ordine recente.</div>
                            <?php endif; ?>
                            <!-- news recenti -->
                            <?php foreach ($recentNews as $rn): ?>
                                <div class="list-group-item d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Notizia</strong>
                                        <div class="small text-muted"><?= esc($rn['headline'] ?? '') ?></div>
                                    </div>
                                    <small
                                        class="text-muted"><?= esc(date('d/m/Y', strtotime($rn['date'] ?? 'now'))) ?></small>
                                </div>
                            <?php endforeach; ?>
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
                            <a href="/admin/ModuleManagementController/" class="btn btn-outline-info"><i
                                    class="fas fa-graduation-cap me-2"></i> Crea Modulo</a>
                            <a href="/admin/UserManagementController/" class="btn btn-outline-warning"><i
                                    class="fas fa-users me-2"></i>
                                Gestisci Utenti</a>
                            <a href="/admin/PortfolioManagementController/" class="btn btn-outline-dark"><i
                                    class="fas fa-wallet me-2"></i> Portafogli</a>
                            <a href="/admin/OrderManagementController/" class="btn btn-outline-secondary"><i
                                    class="fas fa-exchange-alt me-2"></i> Ordini</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>