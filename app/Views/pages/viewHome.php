<?php
$modules = $modules ?? [];
$latestNews = $latestNews ?? [];
$latestModule = $latestModule ?? null;
$latestCompany = $latestCompany ?? null;
$recentCompletion = $recentCompletion ?? null;
$moduleCount = (int) ($moduleCount ?? count($modules));
$companyCount = (int) ($companyCount ?? 0);
?>

<div id="home">
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php if (!empty($latestModule)): ?>
                            <span class="badge bg-primary px-3 py-2">
                                Nuovo modulo: <?= esc($latestModule['name']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($latestCompany)): ?>
                            <span class="badge bg-success px-3 py-2">
                                Nuova societ&agrave;: <?= esc($latestCompany['name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-3 fw-bold mb-4">L'educazione finanziaria, <span class="text-primary">finalmente
                            per tutti.</span></h1>
                    <p class="lead text-muted mb-5">
                        Impara a gestire i tuoi risparmi, analizza societ&agrave; quotate e simula investimenti usando i
                        dati disponibili nella piattaforma.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="/EducationController/index" class="btn btn-primary btn-lg px-4 py-3 shadow">
                            Inizia ad imparare
                        </a>
                        <a href="/CompanyController/index" class="btn btn-outline-dark btn-lg px-4 py-3">
                            Esplora i mercati
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://images.unsplash.com/photo-1611974717482-98252c6a484e?q=80&w=2070&auto=format&fit=crop"
                        class="img-fluid hero-img" alt="Dashboard FinEdu">
                </div>
            </div>
        </div>
    </section>

    <div class="container stats-bar">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="fw-bold text-primary"><?= $companyCount ?></h2>
                    <p class="text-muted mb-0">Societ&agrave; analizzate</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="fw-bold text-primary"><?= $moduleCount ?></h2>
                    <p class="text-muted mb-0">Moduli formativi</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="fw-bold text-primary"><?= count($latestNews) ?></h2>
                    <p class="text-muted mb-0">Notizie recenti</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($recentCompletion)): ?>
        <section class="container mt-4">
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3">
                <i class="fas fa-check-circle fs-3"></i>
                <div>
                    <strong>Ottimo lavoro.</strong>
                    Hai completato <?= esc($recentCompletion['title'] ?? 'una lezione') ?>
                    <?php if (!empty($recentCompletion['module_name'])): ?>
                        nel modulo <?= esc($recentCompletion['module_name']) ?>.
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="py-5 my-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Moduli disponibili</h2>
                <p class="text-muted">Le card arrivano dai moduli attivi salvati nel database.</p>
            </div>

            <?php if (empty($modules)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-graduation-cap text-muted mb-3" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold">Nessun modulo disponibile</h5>
                        <p class="text-muted mb-0">I contenuti formativi saranno pubblicati a breve.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach (array_slice($modules, 0, 6) as $module): ?>
                        <?php $progress = (int) ($module['progress_percent'] ?? 0); ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-feature bg-white">
                                <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h4 class="mb-0"><?= esc($module['name']) ?></h4>
                                    <span class="badge bg-light text-dark border">
                                        <?= (int) ($module['lesson_count'] ?? 0) ?> lezioni
                                    </span>
                                </div>
                                <p class="text-muted small"><?= esc($module['description']) ?></p>
                                <div class="progress mt-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $progress ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted"><?= $progress ?>% completato</small>
                                    <a href="/EducationController/index" class="btn btn-sm btn-outline-primary">
                                        Apri
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container mb-5">
        <div class="edu-banner">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h2 class="display-5 fw-bold mb-4">Dashboard e percorso nello stesso posto.</h2>
                    <p class="fs-5 opacity-75 mb-4">
                        Segui i moduli, consulta societ&agrave; quotate e usa il portafoglio simulato senza lasciare la
                        piattaforma.
                    </p>
                    <a href="/PortfolioController/index" class="btn btn-light btn-lg text-primary fw-bold px-5 py-3">
                        Vai al portafoglio
                    </a>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="card bg-white text-dark p-4 shadow-lg rounded-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle p-2 me-2 text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="fw-bold">Vista dashboard</span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="stat-box">
                                    <strong><?= $moduleCount ?></strong>
                                    <div class="text-muted">moduli</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <strong><?= $companyCount ?></strong>
                                    <div class="text-muted">societ&agrave;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold">Ultime dal mercato</h2>
                    <p class="text-muted mb-0">Le ultime notizie disponibili nel database.</p>
                </div>
                <a href="/CompanyController/index" class="btn btn-link text-primary text-decoration-none fw-bold">
                    Vai alle societ&agrave; <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row">
                <?php if (empty($latestNews)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center text-muted py-4">
                                Nessuna notizia disponibile.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($latestNews as $news): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-primary fw-bold text-uppercase">
                                    <?= esc($news['newspaper'] ?? 'FinEdu') ?>
                                </small>
                                <h5 class="card-title mt-2"><?= esc($news['headline']) ?></h5>
                                <p class="card-text small text-muted"><?= esc($news['subtitle']) ?></p>
                                <hr>
                                <small class="text-muted">
                                    <?= !empty($news['date']) ? esc(date('d/m/Y H:i', strtotime($news['date']))) : '-' ?>
                                    <?php if (!empty($news['author'])): ?>
                                        - <?= esc($news['author']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
