<?php
//modal per effettuare un ordine dalla pagina company.
//mostra il costo stimato (prezzo x qtà) calcolato dinamicamente
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
                            <input type="number" name="quantity" id="orderQty" class="form-control" min="1" value="1" required>
                        </div>
                        <!--stima costo calcolata dinamicamente con il prezzo corrente-->
                        <div class="alert alert-info py-2 mb-0" id="orderCostEstimate">
                            <small>Costo stimato: <strong id="orderCostValue">—</strong></small>
                        </div>
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

<?php if ($canTrade): ?>
<script>
    //calcola il costo stimato quando si apre il modal o si cambia la quantita
    document.addEventListener('DOMContentLoaded', function () {
        //prezzo passato dal controller come displayPrice (testo del prezzo corrente)
        const priceText = '<?= $displayPrice ?? '0' ?>';
        const unitPrice = parseFloat(priceText.replace(',', '.')) || 0;
        const qtyInput = document.getElementById('orderQty');
        const costEl = document.getElementById('orderCostValue');

        const updateCost = () => {
            const qty = parseInt(qtyInput.value) || 1;
            const total = unitPrice * qty;
            costEl.textContent = '€ ' + total.toLocaleString('it-IT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        if (qtyInput) {
            qtyInput.addEventListener('input', updateCost);
        }

        //aggiorna il costo anche quando il modal si apre
        const modal = document.getElementById('addOrderModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', updateCost);
        }
    });
</script>
<?php endif; ?>
