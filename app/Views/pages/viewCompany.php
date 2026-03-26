<div class="container mt-4 mb-5" style="min-height: 80vh;">

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
                <div class="display-6 fw-bold"><?= $company['currency'] ?> 178.50</div>
                <div class="text-muted">Ultimo aggiornamento: 12/02/2026</div>
                <button class="btn btn-success btn-lg mt-2" data-bs-toggle="modal" data-bs-target="#buyModal">
                    <i class="fas fa-shopping-cart"></i> Negozia
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white fw-bold">Andamento Prezzo</div>
                    <div class="card-body">
                        <canvas id="priceChart" height="100"></canvas>
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
                    
                    <div class="tab-pane fade show active" id="financials">
                        <?php if($financialData): ?>
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
                                                    //if percentage
                                                    if (in_array($key, ['net_margin', 'tax_rate'])) {
                                                        echo number_format((float) $value, 2, ',', '.') . '%';
                                                    } else {
                                                        echo $value != 0 ? number_format((float) $value / 1000, 0, ',', '.') : "-";
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

                    <div class="tab-pane fade" id="board">
                        <?php if($board): ?>
                        <div class="row">
                            <?php foreach ($board as $member) : ?>
                            <div class="col-md-6 mb-3">
                                
                                <div class="d-flex align-items-center border p-2 rounded">
                                    <img src="<?= base_url($member['picture_path']) ?>" class="rounded-circle me-3"
                                        width="50" height="50">
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

                    <div class="tab-pane fade" id="shareholders">
                        <?php if($shareholders): ?>
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
                <div class="card mb-4 text-center">
                    <div class="card-header bg-primary text-white">Consensus Analisti</div>
                    <div class="card-body">
                        <h3 class="card-title text-uppercase <?= $averageRating?>"><?= $averageRating ?></h3>
                        <p class="card-text small text-muted">Target Price: <?= $company['currency'] ?> <?= $averageTargetPrice ?></p>
                        <p class="card-text small text-muted"></p>
                    </div>
                    <?php if($consensus): ?>
                    <div class="card-footer p-0">
                        <button class="btn btn-light w-100 p-3 d-flex justify-content-center align-items-center btn-collapse rounded-0 border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConsensus" aria-expanded="false" aria-controls="collapseConsensus">
                          

                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down icon-arrow text-secondary" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
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
                                                        <span class="badge bg-secondary <?= $c['rating']?>_BADGE">
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
                

                <h5 class="border-bottom pb-2">Ultime Notizie</h5>
                <div class="list-group list-group-flush">
                    <!-- Notizia 1 -->
                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal"
                        data-bs-target="#newsModal1">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">Bloomberg</small>
                            <small>10/02/2026</small>
                        </div>
                        <p class="mb-1 fw-bold">Apple supera le aspettative con nuovi iPhone</p>
                    </a>

                    <!-- Notizia 2 -->
                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal"
                        data-bs-target="#newsModal2">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">Reuters</small>
                            <small>08/02/2026</small>
                        </div>
                        <p class="mb-1 fw-bold">Vision Pro 2: in arrivo la seconda generazione</p>
                    </a>

                    <!-- Notizia 3 -->
                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal"
                        data-bs-target="#newsModal3">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">CNBC</small>
                            <small>05/02/2026</small>
                        </div>
                        <p class="mb-1 fw-bold">Apple investe 10 miliardi in AI e machine learning</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal Negozia -->
        <div class="modal fade" id="buyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Inserisci Ordine: AAPL</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="#" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="ticker" value="AAPL">
                            <input type="hidden" name="mic" value="XNAS">
                            <div class="mb-3">
                                <label>Seleziona Portafoglio</label>
                                <select name="portfolio_id" class="form-select">
                                    <option value="1">Risparmi Long Term (Disp: € 3,250.00)</option>
                                    <option value="2">Trading Attivo (Disp: € 1,500.00)</option>
                                    <option value="3">Tech Growth (Disp: € 5,100.25)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Quantità</label>
                                <input type="number" name="quantity" class="form-control" min="1" value="2">
                            </div>
                            <div>
                                <span>Controvalore</span>
                                <span>€ 357,00 </span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" placeholder="Password">
                                <button type="submit" class="btn btn-success">Conferma</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Notizia 1 -->
        <div class="modal fade" id="newsModal1" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Apple supera le aspettative con nuovi iPhone</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>La nuova linea di iPhone registra vendite record nel primo trimestre</h6>
                        <p class="mt-3">Apple Inc. ha annunciato risultati superiori alle aspettative per il primo
                            trimestre fiscale, grazie principalmente al successo della nuova linea di iPhone. Le
                            vendite hanno superato i 95 miliardi di dollari, segnando un incremento del 12% rispetto
                            all'anno precedente. Gli analisti sottolineano la forte domanda nei mercati emergenti e
                            l'entusiasmo per le nuove funzionalità AI integrate nei dispositivi.</p>
                        <small class="text-muted">Autore: Sarah Chen - Bloomberg Technology</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Notizia 2 -->
        <div class="modal fade" id="newsModal2" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Vision Pro 2: in arrivo la seconda generazione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Apple prepara il lancio della nuova versione del suo visore per realtà mista</h6>
                        <p class="mt-3">Secondo fonti vicine all'azienda, Apple starebbe per lanciare la seconda
                            generazione del Vision Pro entro l'estate 2026. Il nuovo dispositivo dovrebbe essere più
                            leggero, con batteria migliorata e un prezzo più accessibile. Tim Cook ha definito la
                            realtà spaziale "il futuro del computing".</p>
                        <small class="text-muted">Autore: Mark Thompson - Reuters</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Notizia 3 -->
        <div class="modal fade" id="newsModal3" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Apple investe 10 miliardi in AI e machine learning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>L'azienda di Cupertino annuncia massicci investimenti in intelligenza artificiale</h6>
                        <p class="mt-3">Apple ha confermato un piano di investimenti da 10 miliardi di dollari in
                            ricerca e sviluppo nel campo dell'intelligenza artificiale e del machine learning. Il CEO
                            Tim Cook ha sottolineato l'importanza strategica dell'AI per il futuro dell'azienda,
                            promettendo nuove funzionalità rivoluzionarie per tutti i prodotti Apple nei prossimi 18
                            mesi.</p>
                        <small class="text-muted">Autore: Jennifer Lee - CNBC</small>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafico Chart.js con dati di esempio
        const ctx = document.getElementById('priceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Prezzo (€)',
                    data: [165, 168, 172, 170, 175, 178, 182, 180, 185, 183, 179, 178.50],
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
