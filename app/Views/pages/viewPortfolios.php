<div class="container mt-4 mb-5" style="min-height: 80vh;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-wallet text-primary me-2"></i>I miei Portafogli</h2>
        <?php if (session()->has('logged')): ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createPortfolioModal">
                <i class="fas fa-plus"></i> Nuovo Portafoglio
            </button>
        <?php endif; ?>
    </div>

    <div class="row" id="portfoliosContainer">
        <?php if (empty($portfolios)): ?>
            <p class="text-muted">Non hai ancora portafogli. Creane uno per iniziare a investire.</p>
        <?php else: ?>
            <?php foreach ($portfolios as $pf): ?>
                <div class="col-md-6 mb-4" data-pf-id="<?= (int) $pf['portfolio_id'] ?>">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title d-flex justify-content-between">
                                <div class="d-flex gap-2 align-items-center portfolio-name-container"
                                    data-portfolio-id="<?= (int) $pf['portfolio_id'] ?>">
                                    <span class="portfolio-name"><?= esc($pf['name']) ?></span>
                                    <button type="button" class="btn btn-link p-0 text-info border-0 shadow-none edit-name-btn">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </div>
                                <span class="badge bg-secondary">ID: <?= (int) $pf['portfolio_id'] ?></span>

                            </h5>
                            <h2 class="my-3" data-field="total_value">€ <?= number_format((float) ($pf['total_value'] ?? 0), 2, ',', '.') ?></h2>
                            <p class="small text-muted mb-2">Valore totale (liquidità + titoli a prezzo di mercato)</p>
                            <div class="row text-center mb-3">
                                <div class="col-6 border-end">
                                    <small class="text-muted">Liquidità</small><br>
                                    <strong data-field="liquidity">€ <?= number_format((int) $pf['liquidity'], 2, ',', '.') ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Investito</small><br>
                                    <strong data-field="invested">€ <?= number_format((int) $pf['invested'], 2, ',', '.') ?></strong>
                                </div>
                            </div>
                            <div class="mb-2 small">
                                <span class="text-muted">P&amp;L non realizzato:</span>
                                <span class="fw-bold" data-field="unrealized_pnl">€
                                    <?= number_format((float) ($pf['unrealized_pnl'] ?? 0), 2, ',', '.') ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-inline gap-2">
                                <a href="/PortfolioController/orders?portfolio_id=<?= (int) $pf['portfolio_id'] ?>" class="btn btn-outline-primary w-75">Storico ordini</a>
                                <a href="/PortfolioController/deletePortfolio?portfolio_id=<?= (int) $pf['portfolio_id'] ?>"
                                    class="btn btn-danger w-25">
                                    <i class="fas fa-trash"></i> Elimina</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createPortfolioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/PortfolioController/createPortfolio" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nuovo portafoglio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" required maxlength="50"
                        placeholder="Es. Risparmio lungo termine">
                    <p class="small text-muted mt-2 mb-0">Capitale iniziale simulato: € 10.000</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Crea</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module" src="/javascript/ajax/portfolioEdit.js"></script>
<script type="module" src="/javascript/ajax/portfolioRefresh.js"></script>