<div id="content-wrapper">
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 fw-bold text-dark">Modifica Società: <span class="text-primary">
                    <?= esc($company['name']) ?>
                </span></h4>
            <small class="text-muted">ISIN:
                <?= esc($company['isin']) ?>
            </small>
        </div>
        <div>
            <a href="<?= base_url('admin/CompanyManagementController/index') ?>"
                class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Torna alla lista</a>
            <a href="<?= base_url('admin/CompanyManagementController/delete/' . esc($company['isin'])) ?>"
                class="btn btn-danger" onclick="return confirm('Sei sicuro di voler disattivare l\'azienda?')">
                <i class="fas fa-trash"></i> Elimina Società
            </a>
        </div>
    </div>

    <div class="main-content pt-0">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom">
                <ul class="nav nav-tabs border-0" id="companyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="info-tab" data-bs-toggle="tab"
                            data-bs-target="#info" type="button" role="tab">Dati Base</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="listings-tab" data-bs-toggle="tab"
                            data-bs-target="#listings" type="button" role="tab">Quotazioni</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="financials-tab" data-bs-toggle="tab"
                            data-bs-target="#financials" type="button" role="tab">Bilanci</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="board-tab" data-bs-toggle="tab" data-bs-target="#board"
                            type="button" role="tab">CdA</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="companyTabContent">

                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form
                            action="<?= base_url('admin/CompanyManagementController/update/' . esc($company['isin'])) ?>"
                            method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ISIN (Non modificabile)</label>
                                    <input type="text" class="form-control bg-light"
                                        value="<?= esc($company['isin']) ?>" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Nome Società</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= esc($company['name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Sito Web</label>
                                    <input type="url" name="website" class="form-control"
                                        value="<?= esc($company['website'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Logo (Sostituisci)</label>
                                    <input type="file" name="logo" class="form-control">
                                    <?php if ($company['logo_path']): ?>
                                        <small class="text-muted">Immagine attuale: <img
                                                src="<?= base_url($company['logo_path']) ?>" width="30"></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Settore</label>
                                    <select name="sector" class="form-select">
                                        <?php foreach ($sectors as $s): ?>
                                            <option value="<?= esc($s['ea_code']) ?>" <?= $s['ea_code'] == $company['ea_code'] ? 'selected' : '' ?>>
                                                <?= esc($s['description']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Paese</label>
                                    <select name="country" class="form-select">
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= esc($c['country_code']) ?>"
                                                <?= $c['country_code'] == $company['country_code'] ? 'selected' : '' ?>>
                                                <?= esc($c['country']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success fw-bold"><i
                                        class="fas fa-save me-2"></i>Salva Dati Base</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="listings" role="tabpanel">
                        <table class="table align-middle table-hover border mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticker</th>
                                    <th>Borsa (MIC)</th>
                                    <th>Valuta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listings)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Nessuna quotazione attiva.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($listings as $listing): ?>
                                        <tr>
                                            <td><span class="badge bg-dark fs-6">
                                                    <?= esc($listing['ticker']) ?>
                                                </span></td>
                                            <td>
                                                <?= esc($listing['mic']) ?> -
                                                <?= esc($listing['full_name']) ?>
                                            </td>
                                            <td>
                                                <?= esc($listing['currency_code']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="financials" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Anno</th>
                                        <th>Valuta</th>
                                        <th>Ricavi</th>
                                        <th>Utile Netto</th>
                                        <th>Debito Netto</th>
                                        <th>FCF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financials as $f): ?>
                                        <tr>
                                            <td><strong>
                                                    <?= esc($f['year']) ?>
                                                </strong></td>
                                            <td>
                                                <?= esc($f['currency_code']) ?>
                                            </td>
                                            <td>
                                                <?= number_format($f['revenues'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-success fw-bold">
                                                <?= number_format($f['net_profit'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-danger">
                                                <?= number_format($f['net_debt'], 0, ',', '.') ?>
                                            </td>
                                            <td class="fw-bold">
                                                <?= number_format($f['free_cash_flow'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="board" role="tabpanel">
                        <div class="row g-3">
                            <?php foreach ($board as $member): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border shadow-sm">
                                        <div class="card-body d-flex align-items-center position-relative">
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                <?= esc(substr($member['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">
                                                    <?= esc($member['full_name']) ?>
                                                </h6>
                                                <span class="text-primary small">
                                                    <?= esc($member['role']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>