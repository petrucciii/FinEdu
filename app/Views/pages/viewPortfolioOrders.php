<?php
//parametri passati dal controller per gestire il dataset iniziale
$filterPfId = (int) ($filterPortfolioId ?? 0);
$baseUrl = '/PortfolioController/orders';
?>

<div class="container mt-4 mb-5" style="min-height: 80vh;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history text-primary me-2"></i>Storico ordini</h2>
        <a href="/PortfolioController/index" class="btn btn-outline-primary">I miei portafogli</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Titolo</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="ordersSearchInput" class="form-control" placeholder="Cerca titolo...">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Stato</label>
                    <select id="ordersStatusFilter" class="form-select form-select-sm">
                        <option value="all">Tutti</option>
                        <option value="1">Aperti</option>
                        <option value="0">Chiusi</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Ordina</label>
                    <select id="ordersSortSelect" class="form-select form-select-sm">
                        <option value="date_open_desc">Data apertura pi&ugrave; recente</option>
                        <option value="date_open_asc">Data apertura meno recente</option>
                        <option value="date_close_desc">Data chiusura pi&ugrave; recente</option>
                        <option value="date_close_asc">Data chiusura meno recente</option>
                        <option value="pnl_desc">P&amp;L maggiore</option>
                        <option value="pnl_asc">P&amp;L minore</option>
                        <option value="quantity_desc">Quantit&agrave; maggiore</option>
                        <option value="quantity_asc">Quantit&agrave; minore</option>
                    </select>
                </div>

                <?php if ($filterPfId === 0 && !empty($portfolios)): ?>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Portafoglio</label>
                        <form method="get" action="<?= $baseUrl ?>">
                            <select name="portfolio_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="0">Tutti i portafogli</option>
                                <?php foreach ($portfolios as $pf): ?>
                                    <option value="<?= (int) $pf['portfolio_id'] ?>">
                                        <?= esc($pf['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="col-md-auto">
                    <button type="button" id="ordersResetFilters" class="btn btn-sm btn-outline-secondary">
                        Reimposta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Data apertura</th>
                    <th>Data chiusura</th>
                    <th>Portafoglio</th>
                    <th>Titolo</th>
                    <th>Qt&agrave;</th>
                    <th>Prezzo acquisto</th>
                    <th>Ultimo / Vendita</th>
                    <th>Stato</th>
                    <th>P&amp;L</th>
                    <th class="text-end">Azione</th>
                </tr>
            </thead>
            <tbody id="ordersBody">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="11" class="text-muted text-center py-4">Nessun ordine.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <?php
                        //dataset usati dal javascript per filtrare e ordinare senza reload
                        $pnlValue = null;
                        if ((int) $o['status'] === 1 && $o['unrealized'] !== null) {
                            $pnlValue = (float) $o['unrealized'];
                        } elseif ($o['realized'] !== null) {
                            $pnlValue = (float) $o['realized'];
                        }
                        $searchText = strtolower($o['ticker'] . ' ' . ($o['exchange_short'] ?? $o['mic']) . ' ' . ($o['company_name'] ?? ''));
                        ?>
                        <tr data-order-row="1" data-order-id="<?= (int) $o['order_id'] ?>"
                            data-search="<?= esc($searchText, 'attr') ?>" data-status="<?= (int) $o['status'] ?>"
                            data-date-open="<?= !empty($o['date']) ? (int) strtotime($o['date']) : 0 ?>"
                            data-date-close="<?= !empty($o['closed_at']) ? (int) strtotime($o['closed_at']) : 0 ?>"
                            data-pnl="<?= $pnlValue !== null ? esc((string) $pnlValue, 'attr') : '' ?>"
                            data-quantity="<?= (int) $o['quantity'] ?>">
                            <td class="text-muted fw-bold">#<?= (int) $o['order_id'] ?></td>
                            <td class="small"><?= esc(date('d/m/Y H:i', strtotime($o['date']))) ?></td>
                            <td class="small text-muted">
                                <?php if (!empty($o['closed_at'])): ?>
                                    <?= esc(date('d/m/Y H:i', strtotime($o['closed_at']))) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= esc($o['portfolio_name'] ?? '') ?></td>
                            <td>
                                <span class="fw-semibold"><?= esc($o['ticker']) ?></span>
                                <span class="text-muted">:<?= esc($o['exchange_short'] ?? $o['mic']) ?></span>
                                <?php if (!empty($o['company_name'])): ?>
                                    <div class="small text-muted"><?= esc($o['company_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-primary"><?= (int) $o['quantity'] ?></td>
                            <td>&euro; <?= number_format((float) $o['buyPrice'], 2, ',', '.') ?></td>
                            <td data-field="last_price">
                                <?php if ((int) $o['status'] === 1): ?>
                                    <?php if ($o['last_price'] !== null): ?>
                                        &euro; <?= number_format((float) $o['last_price'], 2, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    &euro; <?= number_format((float) $o['sellPrice'], 2, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int) $o['status'] === 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                        <i class="fas fa-circle-notch me-1"></i>Aperto
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                        Chiuso
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-field="pnl">
                                <?php if ((int) $o['status'] === 1 && $o['unrealized'] !== null): ?>
                                    <span class="<?= $o['unrealized'] >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                                        &euro; <?= number_format((float) $o['unrealized'], 2, ',', '.') ?>
                                    </span>
                                    <div class="small text-muted">non real.</div>
                                <?php elseif ($o['realized'] !== null): ?>
                                    <span class="<?= $o['realized'] >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                                        &euro; <?= number_format((float) $o['realized'], 2, ',', '.') ?>
                                    </span>
                                    <div class="small text-muted">realizzato</div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ((int) $o['status'] === 1): ?>
                                    <form action="/PortfolioController/close" method="post" class="d-inline"
                                        onsubmit="return confirm('Chiudere la posizione al prezzo di mercato corrente?');">
                                        <input type="hidden" name="order_id" value="<?= (int) $o['order_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Chiudi</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="ordersEmptyFilterRow" class="d-none">
                        <td colspan="11" class="text-muted text-center py-4">Nessun ordine con i filtri selezionati.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script type="module" src="/javascript/ajax/userOrders.js"></script>
<script type="module" src="/javascript/ajax/ordersRefresh.js"></script>
