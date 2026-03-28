<?php
$boardMemberIds     = array_column($board ?? [], 'member_id');
$shareholderFirmIds = array_column($shareholders ?? [], 'firm_id');

$finNumVal = static function ($v) {
    if ($v === null || $v === '') {
        return '';
    }

    return is_numeric($v) ? (string) (int) $v : '';
};
?>
<div id="content-wrapper">

    <?php
    $flashMsg  = session()->getFlashdata('alert');
    $flashType = session()->getFlashdata('alert_type') ?? 'success';
if ($flashMsg):
    $alertClass = $flashType === 'danger' ? 'danger' : 'success';
    $icon       = $flashType === 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle';
    ?>
                <div class="alert alert-<?= esc($alertClass, 'attr') ?> alert-dismissible fade show m-3 shadow-sm" role="alert">
                    <i class="fas <?= esc($icon, 'attr') ?> me-2"></i> <?= esc($flashMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
    <?php endif; ?>

    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 fw-bold text-dark">Modifica Società: <span class="text-primary"><?= esc($company['name']) ?></span></h4>
            <small class="text-muted">ISIN: <?= esc($company['isin']) ?></small>
        </div>
        <div>
            <a href="<?= base_url('admin/CompanyManagementController/index') ?>" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Torna alla lista</a>
            <a href="<?= base_url('admin/CompanyManagementController/delete/' . esc($company['isin'], 'url')) ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler disattivare l\'azienda?')">
                <i class="fas fa-trash"></i> Elimina Società
            </a>
        </div>
    </div>

    <div class="main-content pt-0">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom">
                <ul class="nav nav-tabs border-0" id="companyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Dati base</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#listings" type="button" role="tab">Quotazioni</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#financials" type="button" role="tab">Bilanci</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#board" type="button" role="tab">CdA</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#shareholders" type="button" role="tab">Azionisti</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="companyTabContent">

                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form action="<?= base_url('admin/CompanyManagementController/update/' . esc($company['isin'], 'url')) ?>" method="post" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ISIN (non modificabile)</label>
                                    <input type="text" class="form-control bg-light" value="<?= esc($company['isin']) ?>" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Nome società</label>
                                    <input type="text" name="name" class="form-control" value="<?= esc($company['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Sito web</label>
                                    <input type="url" name="website" class="form-control" value="<?= esc($company['website'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Logo (sostituisci)</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <?php if (! empty($company['logo_path'])): ?>
                                            <small class="text-muted mt-1 d-block">Attuale: <img src="<?= base_url($company['logo_path']) ?>" width="30" class="rounded border" alt=""></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Settore</label>
                                    <select name="sector" class="form-select" required>
                                        <?php foreach ($sectors as $s): ?>
                                                <option value="<?= esc($s['ea_code']) ?>" <?= (int) $s['ea_code'] === (int) $company['ea_code'] ? 'selected' : '' ?>><?= esc($s['description']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Paese</label>
                                    <select name="country" class="form-select" required>
                                        <?php foreach ($countries as $c): ?>
                                                <option value="<?= esc($c['country_code']) ?>" <?= $c['country_code'] === $company['country_code'] ? 'selected' : '' ?>><?= esc($c['country']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Mercato principale (MIC)</label>
                                    <select name="main_exchange" class="form-select" required>
                                        <?php foreach ($exchanges as $ex): ?>
                                                <option value="<?= esc($ex['mic']) ?>" <?= ($company['main_exchange'] ?? '') === $ex['mic'] ? 'selected' : '' ?>><?= esc($ex['mic']) ?> — <?= esc($ex['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-2"></i>Salva dati base</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="listings" role="tabpanel">
                        <h5 class="fw-bold mb-3">Quotazioni (listings)</h5>
                        <form action="<?= base_url('admin/CompanyManagementController/addListing') ?>" method="post" class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Ticker</label>
                                <input type="text" name="ticker" class="form-control form-control-sm text-uppercase" maxlength="5" required placeholder="es. ENI">
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold mb-1">Borsa (MIC)</label>
                                <select name="mic" class="form-select form-select-sm" required>
                                    <?php foreach ($exchanges as $ex): ?>
                                            <option value="<?= esc($ex['mic']) ?>"><?= esc($ex['mic']) ?> — <?= esc($ex['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle table-hover border mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticker</th>
                                        <th>Borsa</th>
                                        <th>Valuta</th>
                                        <th class="text-end">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($listings)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">Nessuna quotazione attiva.</td></tr>
                                <?php else: ?>
                                        <?php foreach ($listings as $listing): ?>
                                                <tr>
                                                    <td><span class="badge bg-dark"><?= esc($listing['ticker']) ?></span></td>
                                                    <td><?= esc($listing['mic']) ?> — <?= esc($listing['full_name']) ?></td>
                                                    <td><?= esc($listing['currency_code']) ?></td>
                                                    <td class="text-end">
                                                        <a href="<?= base_url('admin/CompanyManagementController/deleteListing/' . rawurlencode($listing['ticker']) . '/' . rawurlencode($listing['mic'])) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Rimuovere questa quotazione?')" title="Elimina"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="financials" role="tabpanel">
                        <?php
                        $finAction = base_url('admin/CompanyManagementController/saveFinancial');
$idNew       = 'financial-form-new';
$finCols     = ['revenues', 'amortizations_depretiations', 'income_taxes', 'interests', 'net_profit', 'net_debt', 'share_number', 'free_cash_flow', 'capex', 'dividends'];
?>
                        <form id="<?= esc($idNew, 'attr') ?>" action="<?= esc($finAction, 'attr') ?>" method="post" class="d-none" aria-hidden="true">
                            <input type="hidden" name="isin" value="<?= esc($company['isin'], 'attr') ?>">
                            <input type="hidden" name="is_edit" value="0">
                        </form>
                        <?php foreach ($financials as $f): ?>
                        <form id="<?= esc('financial-form-' . (int) $f['year'], 'attr') ?>" action="<?= esc($finAction, 'attr') ?>" method="post" class="d-none" aria-hidden="true">
                            <input type="hidden" name="isin" value="<?= esc($company['isin'], 'attr') ?>">
                            <input type="hidden" name="is_edit" value="1">
                        </form>
                        <?php endforeach; ?>

                        <h5 class="fw-bold mb-2">Bilanci (tabella <code class="small">data</code>)</h5>
                        <p class="text-muted small mb-3">Ogni riga è un anno (chiave <code class="small">isin</code> + <code class="small">year</code>). Modifica i campi e usa <strong>Salva riga</strong>. Valori vuoti = NULL. Colonne DB:
                            <span class="text-break">revenues, amortizations_depretiations, income_taxes, interests, net_profit, net_debt, share_number, free_cash_flow, capex, dividends</span>.</p>

                        <div class="table-responsive border rounded-3 bg-white" style="max-height:70vh;">
                            <table class="table table-sm table-bordered align-middle mb-0 financial-sheet" style="min-width: 1480px;">
                                <thead class="table-light sticky-top">
                                    <tr class="small text-nowrap">
                                        <th>Anno</th>
                                        <th>type_id</th>
                                        <th>currency_code</th>
                                        <th>revenues</th>
                                        <th>amortizations_depretiations</th>
                                        <th>income_taxes</th>
                                        <th>interests</th>
                                        <th>net_profit</th>
                                        <th>net_debt</th>
                                        <th>share_number</th>
                                        <th>free_cash_flow</th>
                                        <th>capex</th>
                                        <th>dividends</th>
                                        <th class="text-end text-wrap" style="min-width:5.5rem;">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-success bg-opacity-10">
                                        <td colspan="14" class="py-1 small fw-bold">Nuovo esercizio — compila e salva</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td>
                                            <input type="number" form="<?= esc($idNew, 'attr') ?>" name="year" class="form-control form-control-sm" min="1900" max="2100" value="<?= esc((string) (date('Y') - 1)) ?>" required>
                                        </td>
                                        <td>
                                            <select form="<?= esc($idNew, 'attr') ?>" name="type_id" class="form-select form-select-sm" required>
                                                <?php foreach ($data_types as $dt): ?>
                                                        <option value="<?= esc($dt['type_id']) ?>"><?= esc($dt['name']) ?> (<?= esc($dt['type']) ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select form="<?= esc($idNew, 'attr') ?>" name="currency_code" class="form-select form-select-sm" required>
                                                <?php foreach ($currencies as $cur): ?>
                                                        <option value="<?= esc($cur['currency_code']) ?>"><?= esc($cur['currency_code']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <?php foreach ($finCols as $col): ?>
                                        <td><input type="number" form="<?= esc($idNew, 'attr') ?>" name="<?= esc($col, 'attr') ?>" class="form-control form-control-sm" step="1"></td>
                                        <?php endforeach; ?>
                                        <td class="text-end text-nowrap">
                                            <button type="submit" form="<?= esc($idNew, 'attr') ?>" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                                        </td>
                                    </tr>
                                    <?php if (empty($financials)): ?>
                                    <tr><td colspan="14" class="text-center text-muted py-3">Nessun bilancio presente: usa la riga verde per aggiungere il primo anno.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($financials as $f):
                                        $fid = 'financial-form-' . (int) $f['year'];
                            ?>
                                    <tr>
                                        <td>
                                            <input type="number" form="<?= esc($fid, 'attr') ?>" name="year" class="form-control form-control-sm bg-light" value="<?= esc((string) $f['year']) ?>" readonly>
                                        </td>
                                        <td>
                                            <select form="<?= esc($fid, 'attr') ?>" name="type_id" class="form-select form-select-sm" required>
                                                <?php foreach ($data_types as $dt): ?>
                                                        <option value="<?= esc($dt['type_id']) ?>" <?= (int) $f['type_id'] === (int) $dt['type_id'] ? 'selected' : '' ?>><?= esc($dt['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select form="<?= esc($fid, 'attr') ?>" name="currency_code" class="form-select form-select-sm" required>
                                                <?php foreach ($currencies as $cur): ?>
                                                        <option value="<?= esc($cur['currency_code']) ?>" <?= ($f['currency_code'] ?? '') === $cur['currency_code'] ? 'selected' : '' ?>><?= esc($cur['currency_code']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <?php foreach ($finCols as $col): ?>
                                        <td><input type="number" form="<?= esc($fid, 'attr') ?>" name="<?= esc($col, 'attr') ?>" class="form-control form-control-sm" step="1" value="<?= esc($finNumVal($f[$col] ?? null), 'attr') ?>"></td>
                                        <?php endforeach; ?>
                                        <td class="text-end text-nowrap">
                                            <button type="submit" form="<?= esc($fid, 'attr') ?>" class="btn btn-sm btn-success me-1" title="Salva riga"><i class="fas fa-save"></i></button>
                                            <a href="<?= base_url('admin/CompanyManagementController/deleteFinancial/' . (int) $f['year'] . '/' . rawurlencode($company['isin'])) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Eliminare questo bilancio?')" title="Elimina"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="board" role="tabpanel">
                        <h5 class="fw-bold mb-3">Consiglio di amministrazione</h5>

                        <form action="<?= base_url('admin/CompanyManagementController/addBoardMember') ?>" method="post" class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold mb-1">Membro</label>
                                <select name="member_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>— seleziona —</option>
                                    <?php foreach ($all_members as $m): ?>
                                            <?php if (in_array((int) $m['member_id'], array_map('intval', $boardMemberIds), true)) {
                                                continue;
                                            } ?>
                                            <option value="<?= esc($m['member_id']) ?>"><?= esc($m['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold mb-1">Ruolo</label>
                                <input type="text" name="role" class="form-control form-control-sm" maxlength="60" required placeholder="es. Amministratore delegato">
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle border mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Membro</th>
                                        <th>Ruolo e salvataggio</th>
                                        <th class="text-end" style="width:3.5rem;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($board)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">Nessun membro nel CdA.</td></tr>
                                <?php else: ?>
                                        <?php foreach ($board as $member): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= esc($member['full_name']) ?></td>
                                                    <td>
                                                        <form action="<?= base_url('admin/CompanyManagementController/updateBoardMember') ?>" method="post" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                                                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                                                            <input type="hidden" name="member_id" value="<?= esc($member['member_id']) ?>">
                                                            <input type="text" name="role" class="form-control form-control-sm flex-grow-1" style="min-width:12rem;" maxlength="60" value="<?= esc($member['role']) ?>" required>
                                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-save"></i></button>
                                                        </form>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="<?= base_url('admin/CompanyManagementController/deleteBoardMember/' . rawurlencode((string) $member['member_id']) . '/' . rawurlencode($company['isin'])) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Rimuovere dal CdA?')" title="Elimina"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="shareholders" role="tabpanel">
                        <h5 class="fw-bold mb-3">Azionisti</h5>

                        <form action="<?= base_url('admin/CompanyManagementController/addShareholder') ?>" method="post" class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold mb-1">Fondo / società (firms)</label>
                                <select name="firm_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>— seleziona —</option>
                                    <?php foreach ($all_firms as $firm): ?>
                                            <?php if (in_array((int) $firm['firm_id'], array_map('intval', $shareholderFirmIds), true)) {
                                                continue;
                                            } ?>
                                            <option value="<?= esc($firm['firm_id']) ?>"><?= esc($firm['firm_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Quota %</label>
                                <input type="number" name="ownership" class="form-control form-control-sm" step="0.01" min="0" max="100" required placeholder="0,00">
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle border mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fondo / società</th>
                                        <th>Quota % e salvataggio</th>
                                        <th class="text-end" style="width:3.5rem;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($shareholders)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">Nessun azionista registrato.</td></tr>
                                <?php else: ?>
                                        <?php foreach ($shareholders as $sh): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= esc($sh['firm_name']) ?></td>
                                                    <td>
                                                        <form action="<?= base_url('admin/CompanyManagementController/updateShareholder') ?>" method="post" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                                                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                                                            <input type="hidden" name="firm_id" value="<?= esc($sh['firm_id']) ?>">
                                                            <input type="number" name="ownership" class="form-control form-control-sm" style="max-width:8rem;" step="0.01" min="0" max="100" value="<?= esc($sh['ownership']) ?>" required>
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Salva quota"><i class="fas fa-save"></i></button>
                                                        </form>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="<?= base_url('admin/CompanyManagementController/deleteShareholder/' . rawurlencode((string) $sh['firm_id']) . '/' . rawurlencode($company['isin'])) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Rimuovere azionista?')" title="Elimina"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
