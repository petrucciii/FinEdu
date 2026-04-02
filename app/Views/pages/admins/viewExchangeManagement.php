<div id="content-wrapper">

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark"><i class="fas fa-globe text-primary me-2"></i>Borse Valori (Exchanges)</h4>
    </div>

    <div class="main-content pt-0">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold">Aggiungi nuova borsa</h5>
            </div>
            <div class="card-body">
                <form action="/admin/ExchangeManagementController/create" method="post" class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted">MIC</label>
                        <input type="text" name="mic" class="form-control text-uppercase" maxlength="7" required
                            placeholder="XMIL">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted">Abbreviazione</label>
                        <input type="text" name="short_name" class="form-control" required placeholder="MIL">
                    </div>
                    <div class="col-md-12 col-lg-3">
                        <label class="form-label small fw-bold text-muted">Nome completo</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Borsa Italiana">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted">Paese</label>
                        <select name="country_code" class="form-select" required>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= esc($c['country_code']) ?>"><?= esc($c['country']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted">Valuta</label>
                        <select name="currency_code" class="form-select" required>
                            <?php foreach ($currencies as $cur): ?>
                                <option value="<?= esc($cur['currency_code']) ?>"><?= esc($cur['currency_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-1">
                        <label class="form-label small fw-bold text-muted">Apertura</label>
                        <input type="time" name="opening_hour" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-1">
                        <label class="form-label small fw-bold text-muted">Chiusura</label>
                        <input type="time" name="closing_hour" class="form-control">
                    </div>
                    <div class="col-12 col-lg-auto">
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-plus"></i>
                            Inserisci</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div
                class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">Elenco Borse Supportate</h5>
                <div class="input-group input-group-sm" style="max-width: 280px;">
                    <input type="text" id="customSearch" class="form-control" placeholder="Cerca MIC o Nome...">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover w-100" id="exchangesTable">
                        <thead class="table-light">
                            <tr>
                                <th>MIC</th>
                                <th>Nome Completo</th>
                                <th>Abbreviazione</th>
                                <th>Paese</th>
                                <th>Valuta</th>
                                <th>Orari</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exchanges as $ex): ?>
                                <!-- rimpiazz -->
                                <?php $fid = 'ex-form-' . $ex['mic']; ?>
                                <tr class="exchange-row"
                                    data-search="<?= esc(strtolower($ex['mic'] . ' ' . $ex['full_name'] . ' ' . $ex['short_name'] . ' ' . ($ex['country'] ?? ''))) ?>">
                                    <td>
                                        <span class="badge bg-dark fs-6"><?= esc($ex['mic']) ?></span>
                                        <input type="hidden" name="mic" value="<?= esc($ex['mic']) ?>"
                                            form="<?= esc($fid) ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="full_name" class="form-control form-control-sm"
                                            value="<?= esc($ex['full_name']) ?>" form="<?= esc($fid) ?>" required>
                                    </td>
                                    <td>
                                        <input type="text" name="short_name" class="form-control form-control-sm"
                                            value="<?= esc($ex['short_name']) ?>" form="<?= esc($fid) ?>" required>
                                    </td>
                                    <td>
                                        <select name="country_code" class="form-select form-select-sm"
                                            form="<?= esc($fid) ?>">
                                            <?php foreach ($countries as $c): ?>
                                                <option value="<?= esc($c['country_code']) ?>"
                                                    <?= ($c['country_code'] === $ex['country_code']) ? 'selected' : '' ?>>
                                                    <?= esc($c['country']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="currency_code" class="form-select form-select-sm"
                                            form="<?= esc($fid) ?>">
                                            <?php foreach ($currencies as $cur): ?>
                                                <option value="<?= esc($cur['currency_code']) ?>"
                                                    <?= ($cur['currency_code'] === $ex['currency_code']) ? 'selected' : '' ?>>
                                                    <?= esc($cur['currency_code']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <input type="time" name="opening_hour" class="form-control form-control-sm"
                                                value="<?= esc($ex['opening_hour'] ? substr($ex['opening_hour'], 0, 5) : '') ?>"
                                                form="<?= esc($fid) ?>">
                                            <input type="time" name="closing_hour" class="form-control form-control-sm"
                                                value="<?= esc($ex['closing_hour'] ? substr($ex['closing_hour'], 0, 5) : '') ?>"
                                                form="<?= esc($fid) ?>">
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <form id="<?= esc($fid) ?>" action="/admin/ExchangeManagementController/update"
                                            method="post" class="d-inline"></form>
                                        <button type="submit"
                                            class="btn btn-sm btn-light text-primary border shadow-sm me-1"
                                            form="<?= esc($fid) ?>"><i class="fas fa-save"></i></button>
                                        <form action="/admin/ExchangeManagementController/delete" method="post"
                                            class="d-inline" onsubmit="return confirm('Disattivare questa borsa?');">
                                            <input type="hidden" name="mic" value="<?= esc($ex['mic']) ?>">
                                            <button type="submit"
                                                class="btn btn-sm btn-light text-danger border shadow-sm"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        //senza ajax dato che non c'è paginazione e la ricerca viene effettuata solo su campi stampati in tabella
        document.addEventListener('DOMContentLoaded', () => {
            const inp = document.getElementById('customSearch');
            if (!inp) return;
            inp.addEventListener('input', () => {
                const q = inp.value.trim().toLowerCase();
                document.querySelectorAll('.exchange-row').forEach((tr) => {
                    const hay = tr.getAttribute('data-search') || '';
                    tr.style.display = !q || hay.includes(q) ? '' : 'none';
                });
            });
        });
    </script>
</div>