<?php
//mantiene titoli coerenti senza duplicare la logica nei controller
$currentPath = trim((string) service('request')->getUri()->getPath(), '/');
$basePath = trim((string) (parse_url(base_url(), PHP_URL_PATH) ?? ''), '/');
if ($basePath !== '' && strpos($currentPath, $basePath) === 0) {
    $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
}
$currentPath = trim((string) preg_replace('#^index\.php/?#i', '', $currentPath), '/');
$titlePath = strtolower($currentPath);

//mappa i titoli sulle pagine raggiunte con autorouting
$exactTitles = [
    '' => 'Home',
    'home' => 'Home',
    'home/index' => 'Home',
];

$prefixTitles = [
    'admin/dashboardcontroller' => 'Dashboard admin',
    'admin/usermanagementcontroller' => 'Gestione utenti',
    'admin/modulemanagementcontroller/progress' => 'Progressi educazione',
    'admin/modulemanagementcontroller' => 'Gestione moduli',
    'admin/quizmanagementcontroller/editor' => 'Editor quiz',
    'admin/quizmanagementcontroller' => 'Gestione quiz',
    'admin/companymanagementcontroller/edit' => 'Gestione società',
    'admin/companymanagementcontroller' => 'Gestione società',
    'admin/exchangemanagementcontroller' => 'Gestione borse',
    'admin/newsmanagementcontroller' => 'Gestione news',
    'admin/portfoliomanagementcontroller' => 'Gestione portafogli',
    'admin/ordermanagementcontroller' => 'Storico ordini admin',
    'admin/dictionarymanagementcontroller' => 'Tabelle dizionario',
    'companycontroller/viewcompany' => 'Dettaglio società',
    'companycontroller' => 'Analisi mercati',
    'educationcontroller/initialtest' => 'Test iniziale',
    'educationcontroller/module' => 'Modulo educazione',
    'educationcontroller' => 'Educazione finanziaria',
    'listingcontroller' => 'Quotazioni',
    'portfoliocontroller/orders' => 'Storico ordini',
    'portfoliocontroller' => 'I miei portafogli',
    'usercontroller/profile' => 'Profilo',
    'authcontroller' => 'Accesso',
];

$pageTitle = trim((string) ($pageTitle ?? ''));
if ($pageTitle === '') {
    $pageTitle = $exactTitles[$titlePath] ?? '';
}
if ($pageTitle === '') {
    foreach ($prefixTitles as $prefix => $title) {
        if (str_starts_with($titlePath, $prefix)) {
            $pageTitle = $title;
            break;
        }
    }
}

$pageTitle = $pageTitle !== '' ? $pageTitle : 'FinEdu';
$browserTitle = $pageTitle === 'FinEdu' ? 'FinEdu' : $pageTitle . ' | FinEdu';

$request = service('request');
$forwardedHost = trim((string) ($request->getServer('HTTP_X_FORWARDED_HOST') ?? ''));
$forwardedProto = trim((string) ($request->getServer('HTTP_X_FORWARDED_PROTO') ?? ''));
$host = $forwardedHost !== '' ? $forwardedHost : trim((string) ($request->getServer('HTTP_HOST') ?? ''));
$scheme = $forwardedProto !== '' ? $forwardedProto : ($request->isSecure() ? 'https' : 'http');
$hostForCheck = strtolower(trim(explode(':', trim(explode(',', $host)[0]))[0], '[]'));
$useConfiguredBaseUrl = $host === '' || in_array($hostForCheck, ['localhost', '127.0.0.1', '::1'], true);
if ($useConfiguredBaseUrl) {
    $runtimeBaseUrl = rtrim((string) config('App')->baseURL, '/') . '/';
} else {
    $host = trim(explode(',', $host)[0]);
    $scheme = trim(explode(',', $scheme)[0]) ?: 'https';
    $runtimeBaseUrl = $scheme . '://' . $host . '/';
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <title><?= esc($browserTitle) ?></title>
    <meta charset="utf-8">
    <meta name="author" content="f.n.">
    <meta name="description" content="mvc">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('styles/style.css') ?>">
    <script>
        window.baseUrl = '<?= esc($runtimeBaseUrl, 'js') ?>';
        window.appUrl = (path = '') => window.baseUrl.replace(/\/+$/, '') + '/' + String(path).replace(/^\/+/, '');
    </script>
</head>




<body>
    <?php
    /*
     * Calcolo del link "active" nella navbar utente.
     *
     * getPath() restituisce il path reale dell'URL corrente. Lo normalizziamo perche'
     * l'app puo' girare in sottocartella o con index.php nell'URL: senza pulizia il
     * confronto con "EducationController" o "PortfolioController" potrebbe fallire.
     *
     * preg_replace rimuove un eventuale "index.php/" iniziale. La regex significa:
     * - ^: inizio stringa;
     * - index\.php: testo letterale "index.php" (il punto va escapato);
     * - /?: slash opzionale;
     * - i: confronto case-insensitive.
     */
    /*
     * Ritorna true se il path corrente contiene uno dei prefissi passati.
     * Usiamo preg_match con separatori (^|/) e (/|$) per evitare falsi positivi:
     * "PortfolioController" deve attivare il link portafoglio, ma non deve bastare una
     * sottostringa casuale dentro un altro segmento URL.
     */
    $isActivePath = static function (array $prefixes) use ($currentPath): bool {
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
    <?= $this->include('modals/modalAuth') ?>
    <nav class="navbar navbar-expand-lg navbar-dark p-3 bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url() ?>">
                <i class="fas fa-chart-line"></i> FinEdu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $isActivePath(['CompanyController']) ? 'active' : '' ?>"
                            href="<?= base_url('CompanyController/index') ?>">Analisi Mercati</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActivePath(['EducationController']) ? 'active' : '' ?>"
                            href="<?= base_url('EducationController/index') ?>">Educazione Finanziaria</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= $isActivePath(['PortfolioController']) ? 'active' : '' ?>"
                            href="#" id="portfolioDrop" role="button" data-bs-toggle="dropdown">
                            Portafoglio
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= $isActivePath(['PortfolioController/index']) ? 'active' : '' ?>"
                                    href="<?= base_url('PortfolioController/index') ?>">I miei Portafogli</a></li>
                            <li><a class="dropdown-item <?= $isActivePath(['PortfolioController/orders']) ? 'active' : '' ?>"
                                    href="<?= base_url('PortfolioController/orders') ?>">Storico Ordini</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActivePath(['ListingController']) ? 'active' : '' ?>"
                            href="<?= base_url('ListingController/index') ?>">Quotazioni</a>
                    </li>
                </ul>
                <?php
                if (session()->has('logged')):
                    ?>
                    <div class="d-flex">
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle me-2" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                <?= session()->get('first_name') . " " . session()->get('last_name') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item <?= $isActivePath(['UserController/profile']) ? 'active' : '' ?>"
                                        href="<?= base_url('UserController/profile') ?>">Profilo</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= base_url('AuthController/logout') ?>">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php
                else:
                    ?>
                    <div class="d-flex">
                        <button class="btn btn-light text-primary me-2" data-bs-toggle="modal"
                            data-bs-target="#loginModal">Accedi</button>
                        <button class="btn btn-outline-light" data-bs-toggle="modal"
                            data-bs-target="#registerModal">Registrati</button>
                    </div>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </nav>
    <?= $this->include('modals/modalAlert') ?>

    <?php if (session()->get('role_id') == 1 && session()->has('logged')):
        //carico barra di navigaione visibile ad admin.
        //anche se viene caricata in header la position è fixed bottom e non da problemi alle view
        echo view("templates/adminBottombar");
    endif; ?>


    <!-- gestione errori: se vengono ritonati errori di login o registrazione vengono
      aperti forzatamente i Modal e vengono inseirti gli errori -->
    <?php if (isset($login_error) && $login_error != "" || isset($signup_success)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            });
        </script>
        <?php
    elseif (isset($signup_error) && $signup_error != ""): ?>
        <script>

            document.addEventListener("DOMContentLoaded", function () {
                var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
                registerModal.show();
            });

        </script>
        <?php
    endif; ?>
    <!-- quando il modal viene chiuso gli errori vengono rimossi in modo da evitare
     ripetizioni di essi -->
    <script>
        document.getElementById('loginModal').addEventListener('hidden.bs.modal', function () {
            let error = this.querySelector('.text-danger');
            if (error) error.innerHTML = '';
        });

        document.getElementById('registerModal').addEventListener('hidden.bs.modal', function () {
            let error = this.querySelector('.text-danger');
            if (error) error.innerHTML = '';
        });
    </script>
