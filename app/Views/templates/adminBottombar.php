<!-- barra di navigazione amministrativa  -->

<?php
/*
 * Calcolo del link active nella barra admin.
 *
 * La stessa barra viene inclusa in tutte le pagine admin dal template header. Per
 * evitare di impostare manualmente "active" in ogni view, leggiamo il path corrente
 * e lo confrontiamo con i controller gestiti da ogni voce della bottombar.
 */
$currentPath = trim((string) service('request')->getUri()->getPath(), '/');
$basePath = trim((string) (parse_url(base_url(), PHP_URL_PATH) ?? ''), '/');
if ($basePath !== '' && strpos($currentPath, $basePath) === 0) {
    $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
}

/*
 * Rimuove "index.php/" iniziale se CodeIgniter sta generando URL non riscritti.
 * preg_replace qui serve solo a normalizzare l'URL: dopo questa rimozione il confronto
 * funziona sia con /admin/Controller sia con /index.php/admin/Controller.
 */
$currentPath = trim((string) preg_replace('#^index\.php/?#i', '', $currentPath), '/');

$isAdminActive = static function (array $prefixes) use ($currentPath): bool {
    $path = strtolower($currentPath);
    foreach ($prefixes as $prefix) {
        $prefix = strtolower(trim($prefix, '/'));
        if ($prefix !== '' && preg_match('#(^|/)' . preg_quote($prefix, '#') . '(/|$)#', $path)) {
            return true;
        }
    }

    return false;
};
?>

<div id="layout">
    <div id="bottombar" class="fixed-bottom border-top border-secondary">
        <div class="container-fluid h-100">
            <div class="d-flex justify-content-center align-items-center h-100">

                <div class="me-4 d-none d-md-block border-end border-secondary pe-4">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-shield-alt text-primary"></i> FinEdu</h6>
                </div>

                <ul class="nav flex-row justify-content-center align-items-center gap-2 gap-md-4">
                    <li class="nav-item">
                        <a href="/admin/DashboardController/"
                            class="nav-link <?= $isAdminActive(['admin/DashboardController']) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="d-none d-sm-block">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/CompanyManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/CompanyManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-building"></i>
                            <span class="d-none d-sm-block">Società</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/ExchangeManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/ExchangeManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-globe"></i>
                            <span class="d-none d-sm-block">Borse</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/NewsManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/NewsManagementController']) ? 'active' : '' ?>">
                            <i class="far fa-newspaper"></i>
                            <span class="d-none d-sm-block">News</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/ModuleManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/ModuleManagementController', 'admin/QuizManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="d-none d-sm-block">Moduli</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/UserManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/UserManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span class="d-none d-sm-block">Utenti</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/PortfolioManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/PortfolioManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-wallet"></i>
                            <span class="d-none d-sm-block">Portafogli</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/OrderManagementController/"
                            class="nav-link <?= $isAdminActive(['admin/OrderManagementController']) ? 'active' : '' ?>">
                            <i class="fas fa-exchange-alt"></i>
                            <span class="d-none d-sm-block">Ordini</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/AuthController/logout" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="d-none d-sm-block">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
