<?php
$canTrade = session()->has('logged') && !empty($primaryListing) && !empty($userPortfolios);
?>
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Negozia — <?= esc($company['name'] ?? '') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php if (!$canTrade): ?>
                <div class="modal-body">
                    <?php if (!session()->has('logged')): ?>
                        <p class="mb-0">Accedi per operare sul portafoglio.</p>
                    <?php elseif (empty($primaryListing)): ?>
                        <p class="mb-0">Nessun titolo quotato disponibile per questa società.</p>
                    <?php else: ?>
                        <p class="mb-0">Crea prima un portafoglio dalla sezione Portafoglio.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            <?php else: ?>
                <form action="/PortfolioController/buy" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="ticker" value="<?= esc($primaryListing['ticker']) ?>">
                        <input type="hidden" name="mic" value="<?= esc($primaryListing['mic']) ?>">
                        <div class="mb-3">
                            <label class="form-label">Portafoglio</label>
                            <select name="portfolio_id" class="form-select" required>
                                <?php foreach ($userPortfolios as $pf): ?>
                                    <option value="<?= (int) $pf['portfolio_id'] ?>">
                                        <?= esc($pf['name']) ?> — Liq. €
                                        <?= number_format((int) $pf['liquidity'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantità</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <p class="small text-muted mb-0">L'acquisto avviene al prezzo di mercato aggiornato (ultimo prezzo
                            disponibile).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-shopping-cart"></i> Compra</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
