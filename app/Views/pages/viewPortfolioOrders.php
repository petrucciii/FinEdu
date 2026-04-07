<?php
//parametri passati dal controller per gestire filtro portfolio e ordinamento
$filterPfId = $filterPortfolioId ?? 0;
$sort = $currentSort ?? 'orders.date';
$dir = $currentDir ?? 'DESC';
//freccia ordinamento per la colonna data
$dateIcon = ($sort === 'orders.date')
    ? ($dir === 'ASC' ? 'fa-sort-amount-up' : 'fa-sort-amount-down')
    : 'fa-sort-amount-up';
$nextDir = ($sort === 'orders.date' && $dir === 'DESC') ? 'ASC' : 'DESC';
//base url con eventuale filtro portafoglio
$baseUrl = '/PortfolioController/orders';
$pfParam = $filterPfId > 0 ? "portfolio_id={$filterPfId}&" : '';
?>
<div class="container mt-4 mb-5" style="min-height: 80vh;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <!--titolo con icona (lato utente)-->
        <h2><i class="fas fa-history text-primary me-2"></i>Storico ordini</h2>
        <a href="/PortfolioController/index" class="btn btn-outline-primary">I miei portafogli</a>
    </div>

    <!--barra filtri: ricerca ticker/borsa + filtro portafoglio (solo se vista generale)-->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!--ricerca locale per ticker e borsa-->
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <input type="text" id="ordersSearchInput" class="form-control" placeholder="Cerca ticker o borsa...">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <!--filtro per portafoglio: visibile solo se non filtrato da link specifico-->
                <?php if ($filterPfId === 0 && !empty($portfolios)): ?>
                    <form method="get" action="<?= $baseUrl ?>" class="d-flex">
                        <select name="portfolio_id" class="form-select form-select-sm" style="min-width: 180px;"
                            onchange="this.form.submit()">
                            <option value="0">Tutti i portafogli</option>
                            <?php foreach ($portfolios as $pf): ?>
                                <option value="<?= (int) $pf['portfolio_id'] ?>">
                                    <?= esc($pf['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <!--data apertura ordinabile-->
                    <th>
                        <a href="<?= $baseUrl ?>?<?= $pfParam ?>sort=orders.date&dir=<?= $nextDir ?>"
                            class="text-decoration-none text-dark">
                            Data apertura <i class="fas <?= $dateIcon ?> ms-1"></i>
                        </a>
                    </th>
                    <th>Data chiusura</th>
                    <th>Portafoglio</th>
                    <th>Titolo</th>
                    <th>Qtà</th>
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
                        <!--data-search contiene ticker + borsa per la ricerca lato client-->
                        <tr data-order-id="<?= (int) $o['order_id'] ?>"
                            data-search="<?= esc(strtolower($o['ticker'] . ' ' . ($o['exchange_short'] ?? $o['mic']) . ' ' . ($o['company_name'] ?? ''))) ?>">
                            <td class="text-muted fw-bold">#<?= (int) $o['order_id'] ?></td>
                            <td class="small"><?= esc(date('d/m/Y H:i', strtotime($o['date']))) ?></td>
                            <td class="small text-muted">
                                <?php if (!empty($o['closed_at'])): ?>
                                    <?= esc(date('d/m/Y H:i', strtotime($o['closed_at']))) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= esc($o['portfolio_name'] ?? '') ?></td>
                            <td>
                                <!--ticker con short_name della borsa al posto del mic-->
                                <span class="fw-semibold"><?= esc($o['ticker']) ?></span>
                                <span class="text-muted">:<?= esc($o['exchange_short'] ?? $o['mic']) ?></span>
                                <?php if (!empty($o['company_name'])): ?>
                                    <div class="small text-muted"><?= esc($o['company_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-primary"><?= (int) $o['quantity'] ?></td>
                            <td>€ <?= number_format((float) $o['buyPrice'], 2, ',', '.') ?></td>
                            <td data-field="last_price">
                                <?php if ((int) $o['status'] === 1): ?>
                                    <?php if ($o['last_price'] !== null): ?>
                                        € <?= number_format((float) $o['last_price'], 2, ',', '.') ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    € <?= number_format((float) $o['sellPrice'], 2, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int) $o['status'] === 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i
                                            class="fas fa-circle-notch me-1"></i>Aperto</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Chiuso</span>
                                <?php endif; ?>
                            </td>
                            <td data-field="pnl">
                                <?php if ((int) $o['status'] === 1 && $o['unrealized'] !== null): ?>
                                    <span class="<?= $o['unrealized'] >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">€
                                        <?= number_format((float) $o['unrealized'], 2, ',', '.') ?>
                                    </span>
                                    <div class="small text-muted">non real.</div>
                                <?php elseif ($o['realized'] !== null): ?>
                                    <span class="<?= $o['realized'] >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">€
                                        <?= number_format((float) $o['realized'], 2, ',', '.') ?>
                                    </span>
                                    <div class="small text-muted">realizzato</div>
                                <?php else: ?>
                                    —
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
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!--ricerca client-side per ticker e borsa (come in exchangeManagement)-->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inp = document.getElementById('ordersSearchInput');
        if (!inp) return;
        inp.addEventListener('input', () => {
            const q = inp.value.trim().toLowerCase();
            document.querySelectorAll('#ordersBody tr[data-search]').forEach(tr => {
                const hay = tr.getAttribute('data-search') || '';
                tr.style.display = !q || hay.includes(q) ? '' : 'none';
            });
        });
    });
</script>

<script type="module" src="/javascript/ajax/ordersRefresh.js"></script>
