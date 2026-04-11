<div class="container mt-4 mb-5" style="min-height: 80vh;">
    <!-- header -->
    <div class="row mb-4">
        <div class="col-md-1 d-flex align-items-center justify-content-center">
            <img src="<?= $company['logo_path'] ?>" class="img-fluid rounded" alt="Logo">
        </div>
        <div class="col-md-7">
            <h1 class="display-5 fw-bold">
                <?= $company['name'] ?>
            </h1>
            <span class="badge bg-primary"><?= $company['sector'] ?></span>
            <span class="badge bg-info text-dark"><?= $company['country'] ?></span>
            <a href="<?= $company['website'] ?>" target="_blank" class="text-decoration-none ms-2">
                <i class="fas fa-link"></i> <?= $company['website'] ?>
            </a>
        </div>
        <div class="col-md-4 text-end">
            <div class="display-6 fw-bold">
                <?= esc($company['currency'] ?? '€') ?>
                <?= esc($displayPrice) ?>
            </div>
            <div class="text-muted">Ultimo aggiornamento:
                <?= esc($displayPriceUpdate) ?>
            </div>
            <button class="btn btn-success btn-lg mt-2" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-shopping-cart"></i> Negozia
            </button>
        </div>
    </div>

    <!-- grafico -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Andamento Prezzo <span id="chartVariation" class="ms-2 small"></span></span>
                    <div class="btn-group btn-group-sm" role="group" id="chartRangeGroup">
                        <button type="button" class="btn btn-outline-primary chart-range-btn"
                            data-range="3M">3M</button>
                        <button type="button" class="btn btn-outline-primary chart-range-btn"
                            data-range="6M">6M</button>
                        <button type="button" class="btn btn-outline-primary chart-range-btn active"
                            data-range="1Y">1Y</button>
                        <button type="button" class="btn btn-outline-primary chart-range-btn"
                            data-range="MAX">MAX</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- dati da passare al grafico -->
                    <canvas id="priceChart" height="100" data-isin="<?= esc($company['isin']) ?>"
                        data-currency="<?= esc($company['currency'] ?? '€') ?>"
                        data-labels='<?= json_encode($chartLabels) ?>'
                        data-values='<?= json_encode($chartValues) ?>'></canvas>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" id="companyTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#financials">Dati Finanziari</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#board">CdA</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#shareholders">Azionariato</a>
                </li>
            </ul>

            <div class="tab-content bg-white p-3 border border-top-0 rounded-bottom shadow-sm">
            <!-- bilanci -->
                <div class="tab-pane fade show active" id="financials">
                    <?php if ($financialData): ?>
                        <h5 class="mb-3">Bilancio (Dati in <?= $financialData['currency_code'] ?> Migliaia)</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Voce</th>
                                        <?php foreach ($financialData['years'] as $yearLabel): ?>
                                            <th class="text-end"><?= esc($yearLabel) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financialData['rows'] as $key => $rowData): ?>
                                        <tr>
                                            <td>
                                                <?= esc($rowData['label']) ?>
                                            </td>

                                            <?php foreach ($rowData['values'] as $year => $value): ?>
                                                <td class="text-end <?= $key === 'net_profit' ? 'fw-bold' : '' ?>">
                                                    <?php
                                                    // controllo ESATTO per il trattino (i NULL del DB)
                                                    if ($value === '-') {
                                                        echo '-';
                                                    } else {
                                                        if (in_array($key, ['net_margin', 'tax_rate'])) {
                                                            echo number_format((float) $value, 2, ',', '.') . '%';
                                                        } else {
                                                            echo number_format((float) $value / 1000, 0, ',', '.');
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <h3>Nessun Dato Disponibile</h3>
                    <?php endif; ?>
                </div>
                <!-- CdA -->
                <div class="tab-pane fade" id="board">
                    <?php if ($board): ?>
                        <div class="row">
                            <?php foreach ($board as $member): ?>
                                <div class="col-md-6 mb-3">

                                    <div class="d-flex align-items-center border p-2 rounded">
                                        <img src="<?= base_url($member['picture_path']) ?>" alt="Picture"
                                            class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <h6 class="mb-0"><?= $member['full_name'] ?></h6>
                                            <small class="text-muted"><?= $member['role'] ?></small>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <h3>Nessun Dato Disponibile</h3>
                    <?php endif; ?>
                </div>
                <!-- Azionisti -->
                <div class="tab-pane fade" id="shareholders">
                    <?php if ($shareholders): ?>
                        <ul class="list-group">
                            <?php foreach ($shareholders as $firm): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $firm['firm_name'] ?>
                                    <span class="badge bg-primary rounded-pill"><?= $firm['ownership'] ?>%</span>
                                </li>
                            <?php endforeach; ?>


                        </ul>
                    <?php else: ?>
                        <h3>Nessun Dato Disponibile</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Consensus Analisti -->
            <div class="card mb-4 text-center">
                <div class="card-header bg-primary text-white">Consensus Analisti</div>
                <div class="card-body">
                    <!-- average rating e tp se non disponibili N/A (controllo fatto dal server) -->
                    <h3 class="card-title text-uppercase <?= $averageRating ?>"><?= $averageRating ?></h3>
                    <p class="card-text small text-muted">Target Price: <?= $company['currency'] ?>
                        <?= $averageTargetPrice ?>
                    </p>
                    <p class="card-text small text-muted"></p>
                </div>
                <!-- solo se c'è consensus viene mostrato bottone e tabella -->
                <?php if ($consensus): ?>
                    <div class="card-footer p-0">
                        <button
                            class="btn btn-light w-100 p-3 d-flex justify-content-center align-items-center btn-collapse rounded-0 border-0 shadow-none"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapseConsensus"
                            aria-expanded="false" aria-controls="collapseConsensus">


                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-chevron-down icon-arrow text-secondary" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </button>


                        <div class="collapse" id="collapseConsensus">
                            <div class="p-3 pt-0 bg-white">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-sm mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Firm</th>
                                                <th scope="col">Rating</th>
                                                <th scope="col">Data</th>
                                                <th scope="col" class="text-end">Target Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($consensus as $c): ?>
                                                <tr>
                                                    <td class="fw-semibold text-nowrap">
                                                        <?= htmlspecialchars($c['firm_name']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary <?= $c['rating'] ?>_BADGE">
                                                            <?= htmlspecialchars($c['rating']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small text-nowrap">
                                                        <?= htmlspecialchars($c['date']) ?>
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        <?= htmlspecialchars($c['target_price']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>

            <!-- ultime N notizie passate -->
            <h5 class="border-bottom pb-2">Ultime Notizie</h5>
            <div class="list-group list-group-flush">
                <?php if (!empty($latestNews)): ?>
                    <?php foreach ($latestNews as $nw): ?>
                        <a href="#" class="list-group-item list-group-item-action open-company-news"
                            data-news-id="<?= (int) $nw['news_id'] ?>" data-isin="<?= esc($company['isin']) ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <small class="text-muted"><?= esc($nw['newspaper'] ?? '') ?></small>
                                <small><?= esc(date('d/m/Y', strtotime($nw['date']))) ?></small>
                            </div>
                            <p class="mb-1 fw-bold"><?= esc($nw['headline']) ?></p>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-muted small">Nessuna notizia collegata.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?= $this->include('modals/modalOrderAdd') ?>
    <?= $this->include('modals/modalCompanyNews') ?>

</div>
<!--  -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    //script per modal norizie, fetch ajax per ottenere newsBody formattato e altri campi news. 
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('companyNewsModal');
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        document.querySelectorAll('.open-company-news').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                const id = el.getAttribute('data-news-id');
                const isin = encodeURIComponent(el.getAttribute('data-isin'));
                fetch('/CompanyController/newsBody/' + isin + '/' + id)//ajax 
                    .then((r) => {
                        if (!r.ok) throw new Error();
                        return r.json();
                    })
                    .then((d) => {//riempie moda viewCompanyNews
                        modalEl.querySelector('.modal-title').textContent = d.headline || '';
                        const sub = modalEl.querySelector('[data-news-field="subtitle"]');
                        const body = modalEl.querySelector('[data-news-field="body"]');
                        const meta = modalEl.querySelector('[data-news-field="meta"]');
                        if (sub) sub.textContent = d.subtitle || '';
                        if (body) body.innerHTML = d.body || '';
                        const dt = d.date ? new Date(d.date).toLocaleString('it-IT') : '';
                        if (meta) meta.textContent = (d.newspaper ? d.newspaper + ' — ' : '') + dt + (d.author ? ' — ' + d.author : '');//data, giornale e autore
                        modal.show();
                    })
                    .catch(() => alert('Impossibile caricare la notizia'));
            });
        });
    });
</script>

<script src="/javascript/priceChart.js"></script>