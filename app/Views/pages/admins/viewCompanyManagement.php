<?php
//elenco ID già usati per escludere le option nelle form di aggiunta
$boardMemberIds = array_column($board ?? [], 'member_id');//array_column prende colonna member_id e riempie nuovo array con quei valori
$shareholderFirmIds = array_column($shareholders ?? [], 'firm_id');
//etichette colonne bilancio (stessi testi di CompanyController::buildFinancialArray)
$financialLabels = $financialLabels ?? [];

//formatta numeri bilancio, ritorna stringa. se null stringa vuota. server per evitare che campi numerici mostrino 0 o valori strani.
$finNumVal = static function ($v) {
    if ($v === null || $v === '') {
        return '';
    }

    return is_numeric($v) ? (string) (int) $v : '';
};
?>
<div id="content-wrapper">


    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 fw-bold text-dark">Modifica Società: <span
                    class="text-primary"><?= esc($company['name']) ?></span></h4>
            <!-- esc previene xss(codice html o js che verrebbe trattato come elementi dom), converte in semplice testo il contenuto-->
            <small class="text-muted">ISIN: <?= esc($company['isin']) ?></small>

        </div>
        <div>
            <a href="<?= base_url('admin/CompanyManagementController/index') ?>"
                class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i> Torna alla lista</a>
            <a href="<?= base_url('admin/CompanyManagementController/delete/' . esc($company['isin'], 'url')) ?>"
                class="btn btn-danger" onclick="return confirm('Sei sicuro di voler disattivare l\'azienda?')">
                <i class="fas fa-trash"></i> Elimina Società
            </a>
        </div>
    </div>

    <div class="main-content pt-0">
        <div class="card border-0 shadow-sm rounded-4">
            <!-- INIZIO BARRA DI NAVIGAZIONE TRA VARIE FUNZIONALITà di MODIFICA E INSERIMENTO -->
            <div class="card-header bg-white pt-4 pb-0 border-bottom">
                <!-- tab bootstrap per gestione varie schede -->
                <ul class="nav nav-tabs border-0" id="companyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#info"
                            type="button" role="tab">Dati base</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#listings" type="button"
                            role="tab">Quotazioni</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#financials" type="button"
                            role="tab">Bilanci</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#board" type="button"
                            role="tab">CdA</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#shareholders"
                            type="button" role="tab">Azionisti</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#consensus" type="button"
                            role="tab">Consensus</button>
                    </li>
                </ul>
            </div>
            <!-- FINE BARRA DI NAVIGAZIONE -->

            <div class="card-body p-4">
                <div class="tab-content" id="companyTabContent">
                    <!-- INIZIO DATI BASE -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <!-- modifica dati base -->
                        <form
                            action="<?= base_url('admin/CompanyManagementController/update/' . esc($company['isin'], 'url')) ?>"
                            method="post" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ISIN (non modificabile)</label>
                                    <input type="text" class="form-control bg-light"
                                        value="<?= esc($company['isin']) ?>" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Nome società</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= esc($company['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Sito web</label>
                                    <input type="url" name="website" class="form-control"
                                        value="<?= esc($company['website'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Logo</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <?php if (!empty($company['logo_path'])): ?> <!--mostra immagine attuale -->
                                        <small class="text-muted mt-1 d-block">Attuale: <img
                                                src="<?= base_url($company['logo_path']) ?>" width="30"
                                                class="rounded border" alt=""></small>
                                    <?php endif; ?>
                                </div>
                                <!-- lista settori -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Settore</label>
                                    <select name="sector" class="form-select" required>
                                        <?php foreach ($sectors as $s): ?>
                                            <option value="<?= esc($s['ea_code']) ?>" <?= (int) $s['ea_code'] === (int) $company['ea_code'] ? 'selected' : '' ?>><?= esc($s['description']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- lista paesi -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Paese</label>
                                    <select name="country" class="form-select" required>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= esc($c['country_code']) ?>"
                                                <?= $c['country_code'] === $company['country_code'] ? 'selected' : '' ?>>
                                                <?= esc($c['country']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- lista borse -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Mercato principale (MIC)</label>
                                    <select name="main_exchange" class="form-select" required>
                                        <?php foreach ($exchanges as $ex): ?>
                                            <option value="<?= esc($ex['mic']) ?>" <?= ($company['main_exchange'] ?? '') === $ex['mic'] ? 'selected' : '' ?>><?= esc($ex['mic']) ?> —
                                                <?= esc($ex['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success fw-bold"><i
                                        class="fas fa-save me-2"></i>Salva dati base</button>
                            </div>
                        </form>
                    </div>
                    <!-- FINE DATI DI BASE -->

                    <!-- INIZIO QUOTAZIONI -->
                    <div class="tab-pane fade" id="listings" role="tabpanel">
                        <h5 class="fw-bold mb-3">Quotazioni (listings)</h5>
                        <!-- aggiunta quotazioni -->
                        <form action="<?= base_url('admin/CompanyManagementController/addListing') ?>" method="post"
                            class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <!-- isin nascosto da passare al server -->
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-6 col-md-3">
                                <!-- ticker -->
                                <label class="form-label small fw-bold mb-1">Ticker</label>
                                <input type="text" name="ticker" class="form-control form-control-sm text-uppercase"
                                    maxlength="5" required placeholder="es. ENI">
                            </div>
                            <div class="col-12 col-md-5">
                                <!-- borsa (lista) -->
                                <label class="form-label small fw-bold mb-1">Borsa (MIC)</label>
                                <select name="mic" class="form-select form-select-sm" required>
                                    <?php foreach ($exchanges as $ex): ?>
                                        <option value="<?= esc($ex['mic']) ?>"><?= esc($ex['mic']) ?> —
                                            <?= esc($ex['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i
                                        class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>
                        <!-- elenco quotazioni esistenti -->
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
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Nessuna quotazione attiva.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listings as $listing): ?>
                                            <tr>
                                                <td><span class="badge bg-dark"><?= esc($listing['ticker']) ?></span></td>
                                                <td><?= esc($listing['mic']) ?> — <?= esc($listing['full_name']) ?></td>
                                                <td><?= esc($listing['currency_code']) ?></td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('admin/CompanyManagementController/deleteListing/' . rawurlencode($listing['ticker']) . '/' . rawurlencode($listing['mic'])) ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Rimuovere questa quotazione?')"
                                                        title="Elimina"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- FINE QUOTAZIONI -->

                    <!-- INIZIO BILANCI -->
                    <div class="tab-pane fade" id="financials" role="tabpanel">
                        <?php
                        //definisce l'endpoint di salvataggio e le colonne numeriche mappate sulla tabella data del database
                        $finAction = base_url('admin/CompanyManagementController/saveFinancial');
                        $idNew = 'financial-form-new';
                        $finCols = ['revenues', 'amortizations_depretiations', 'income_taxes', 'interests', 'net_profit', 'net_debt', 'share_number', 'free_cash_flow', 'capex', 'dividends'];
                        $finColspan = 3 + count($finCols) + 1;
                        ?>

                        <!-- per motivi di design e ordine nel codice viene inizializzato un form (e successivamente anche per le modifiche dei bilanci) invisibile a cui verranno poi inserirti
                         i vari campi per un nuovo record (bilancio) -->
                        <form id="<?= esc($idNew, 'attr') ?>" action="<?= esc($finAction, 'attr') ?>" method="post"
                            class="d-none" aria-hidden="true">
                            <input type="hidden" name="isin" value="<?= esc($company['isin'], 'attr') ?>">
                            <input type="hidden" name="is_edit" value="0">
                        </form>

                        <!-- genera un form nascosto per ogni bilancio esistente per permettere l'aggiornamento (edit) via id form -->
                        <?php foreach ($financials as $f): ?>
                            <form id="<?= esc('financial-form-' . (int) $f['year'], 'attr') ?>"
                                action="<?= esc($finAction, 'attr') ?>" method="post" class="d-none" aria-hidden="true">
                                <input type="hidden" name="isin" value="<?= esc($company['isin'], 'attr') ?>">
                                <input type="hidden" name="is_edit" value="1">
                            </form>
                        <?php endforeach; ?>

                        <h5 class="fw-bold mb-2">Bilanci</h5>

                        <!-- sezione dedicata all'importazione massiva dei dati finanziari tramite file xml -->
                        <div class="card border mb-3">
                            <div class="card-body py-3">
                                <h6 class="small fw-bold mb-2">Import da file XML</h6>
                                <form action="<?= base_url('admin/CompanyManagementController/importFinancialXml') ?>"
                                    method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
                                    <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">

                                    <!-- selezione del tipo di dato (es. annuale, trimestrale) per l'import xml -->
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small mb-0">Tipo dato</label>
                                        <select name="type_id" class="form-select form-select-sm" required>
                                            <?php foreach ($data_types as $dt): ?>
                                                <option value="<?= esc($dt['type_id']) ?>"><?= esc($dt['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- selezione della valuta di riferimento dei valori contenuti nell'xml -->
                                    <div class="col-12 col-md-2">
                                        <label class="form-label small mb-0">Valuta</label>
                                        <select name="currency_code" class="form-select form-select-sm" required>
                                            <?php foreach ($currencies as $cur): ?>
                                                <option value="<?= esc($cur['currency_code']) ?>">
                                                    <?= esc($cur['currency_code']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- campo per il caricamento del file xml -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small mb-0">File XML</label>
                                        <input type="file" name="xml_file" class="form-control form-control-sm"
                                            accept=".xml,text/xml,application/xml" required>
                                    </div>

                                    <div class="col-12 col-md-auto">
                                        <button type="submit" class="btn btn-sm btn-primary fw-bold"><i
                                                class="fas fa-file-import me-1"></i>Importa</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- tabella principale per la visualizzazione e l'editing manuale dei singoli esercizi -->
                        <div class="table-responsive border rounded-3 bg-white" style="max-height:70vh;">
                            <table class="table table-sm table-bordered align-middle mb-0 financial-sheet"
                                style="min-width: 1480px;">
                                <thead class="table-light sticky-top">
                                    <tr class="small text-nowrap">
                                        <th>Anno</th>
                                        <th>Tipo dato</th>
                                        <th>Valuta</th>
                                        <!-- cicla le etichette dei campi finanziari per le intestazioni della tabella -->
                                        <?php foreach ($finCols as $col): ?>
                                            <th title="<?= esc($col) ?>"><?= esc($financialLabels[$col] ?? $col) ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-end text-wrap" style="min-width:5.5rem;">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <!-- riga evidenziata in verde per l'inserimento manuale di un nuovo esercizio -->
                                    <tr class="table-success bg-opacity-10">
                                        <td colspan="<?= (int) $finColspan ?>" class="py-1 small fw-bold">Nuovo
                                            esercizio — compila e salva</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <!-- l'attributo form="idForm" è utilizzato per associare i vari input dentro il
                                          form invisibile inizializzato in precedenza (con lo stesso id) -->
                                        <td>
                                            <input type="number" form="<?= esc($idNew, 'attr') ?>" name="year"
                                                class="form-control form-control-sm" min="1900" max="2100"
                                                value="<?= esc((string) (date('Y') - 1)) ?>" required>
                                        </td>
                                        <td>
                                            <select form="<?= esc($idNew, 'attr') ?>" name="type_id"
                                                class="form-select form-select-sm" required>
                                                <?php foreach ($data_types as $dt): ?>
                                                    <option value="<?= esc($dt['type_id']) ?>"><?= esc($dt['name']) ?>
                                                        (<?= esc($dt['type']) ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select form="<?= esc($idNew, 'attr') ?>" name="currency_code"
                                                class="form-select form-select-sm" required>
                                                <?php foreach ($currencies as $cur): ?>
                                                    <option value="<?= esc($cur['currency_code']) ?>">
                                                        <?= esc($cur['currency_code']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <!-- campi input per i valori numerici del nuovo bilancio (collegati al form nuovo) -->
                                        <?php foreach ($finCols as $col): ?>
                                            <td><input type="number" form="<?= esc($idNew, 'attr') ?>"
                                                    name="<?= esc($col, 'attr') ?>" class="form-control form-control-sm"
                                                    step="1"></td>
                                        <?php endforeach; ?>
                                        <td class="text-end text-nowrap">
                                            <button type="submit" form="<?= esc($idNew, 'attr') ?>"
                                                class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                                        </td>
                                    </tr>

                                    <!-- messaggio se non sono presenti dati in tabella financials -->
                                    <?php if (empty($financials)): ?>
                                        <tr>
                                            <td colspan="<?= (int) $finColspan ?>" class="text-center text-muted py-3">
                                                Nessun bilancio presente: usa la riga verde per aggiungere il primo anno.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <!-- ciclo per renderizzare ogni riga di bilancio presente nel database 
                                     anche qui form="idFormRiferimento"-->

                                    <?php foreach ($financials as $f):
                                        $fid = 'financial-form-' . (int) $f['year'];
                                        ?>
                                        <tr>
                                            <td>
                                                <input type="number" form="<?= esc($fid, 'attr') ?>" name="year"
                                                    class="form-control form-control-sm bg-light"
                                                    value="<?= esc((string) $f['year']) ?>" readonly>
                                            </td>
                                            <td>
                                                <select form="<?= esc($fid, 'attr') ?>" name="type_id"
                                                    class="form-select form-select-sm" required>
                                                    <?php foreach ($data_types as $dt): ?>
                                                        <option value="<?= esc($dt['type_id']) ?>" <?= (int) $f['type_id'] === (int) $dt['type_id'] ? 'selected' : '' ?>>
                                                            <?= esc($dt['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select form="<?= esc($fid, 'attr') ?>" name="currency_code"
                                                    class="form-select form-select-sm" required>
                                                    <?php foreach ($currencies as $cur): ?>
                                                        <option value="<?= esc($cur['currency_code']) ?>"
                                                            <?= ($f['currency_code'] ?? '') === $cur['currency_code'] ? 'selected' : '' ?>><?= esc($cur['currency_code']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <!-- popola i campi di input con i valori correnti del bilancio per la modifica -->
                                            <?php foreach ($finCols as $col): ?>
                                                <td><input type="number" form="<?= esc($fid, 'attr') ?>"
                                                        name="<?= esc($col, 'attr') ?>" class="form-control form-control-sm"
                                                        step="1" value="<?= esc($finNumVal($f[$col] ?? null), 'attr') ?>"></td>
                                            <?php endforeach; ?>

                                            <!-- azioni di salvataggio riga o eliminazione definitiva del bilancio -->
                                            <td class="text-end text-nowrap">
                                                <button type="submit" form="<?= esc($fid, 'attr') ?>"
                                                    class="btn btn-sm btn-success me-1" title="Salva riga"><i
                                                        class="fas fa-save"></i></button>
                                                <a href="<?= base_url('admin/CompanyManagementController/deleteFinancial/' . (int) $f['year'] . '/' . rawurlencode($company['isin'])) ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Eliminare questo bilancio?')"
                                                    title="Elimina"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- FINE BILANCI -->

                    <!-- INIZIO CDA -->
                    <div class="tab-pane fade" id="board" role="tabpanel">
                        <h5 class="fw-bold mb-3">Consiglio di Amministrazione</h5>
                        <!-- form per aggiungere nuovi membri cda -->
                        <form action="<?= base_url('admin/CompanyManagementController/addBoardMember') ?>" method="post"
                            class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold mb-1">Membro</label>
                                <!-- lista persone esistenti -->
                                <select name="member_id" id="boardMemberSelect" class="form-select form-select-sm"
                                    required>
                                    <option value="" disabled selected>— Seleziona —</option>
                                    <?php foreach ($all_members as $m): ?>
                                        <!-- non mostra membri già nel CdA, array_map  -->
                                        <?php if (in_array((int) $m['member_id'], array_map('intval', $boardMemberIds), true)) {
                                            continue;
                                        } ?>
                                        <option value="<?= esc($m['member_id']) ?>"><?= esc($m['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold mb-1">Ruolo</label>
                                <input type="text" name="role" class="form-control form-control-sm" maxlength="60"
                                    required placeholder="es. Amministratore delegato">
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i
                                        class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>

                        <!-- lista cda -->
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
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Nessun membro nel CdA.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($board as $member): ?>
                                            <tr>
                                                <td>

                                                    <!--sezione dedicata alla visualizzazione: immagine del profilo e nome del
                                                        membro -->
                                                    <div class="d-flex align-items-center" style="min-width: 250px;">
                                                        <!-- immagine rotonda con fallback se il percorso non esiste o è vuoto -->
                                                        <img src="<?= base_url($member['picture_path'] ?: 'assets/img/default-avatar.png') ?>"
                                                            class="rounded-circle me-2 border"
                                                            style="width: 40px; height: 40px; object-fit: cover;" alt="Profile">

                                                        <span class="fw-bold small">
                                                            <?= esc($member['full_name']) ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <form
                                                        action="<?= base_url('admin/CompanyManagementController/updateBoardMember') ?>"
                                                        method="post"
                                                        class="d-flex flex-wrap gap-3 align-items-center mb-0 p-2 border-bottom">

                                                        <!-- campi nascosti per identificare l'azienda e il membro del board
                                                            durante il salvataggio -->
                                                        <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                                                        <input type="hidden" name="member_id"
                                                            value="<?= esc($member['member_id']) ?>">


                                                        <div class="flex-grow-1">
                                                            <input type="text" name="role" class="form-control form-control-sm"
                                                                placeholder="Inserisci ruolo..." maxlength="60"
                                                                value="<?= esc($member['role']) ?>" required>
                                                        </div>

                                                        <div class="d-flex gap-1">
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                title="Salva modifiche">
                                                                <i class="fas fa-save"></i>
                                                            </button>

                                                            <a href="<?= base_url('admin/CompanyManagementController/deleteBoardMember/' . (int) $member['member_id'] . '/' . esc($company['isin'])) ?>"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Rimuovere questo membro?')"
                                                                title="Elimina">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </form>
                                                </td>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- FINE CDA -->

                    <!-- INIZIO AZIONISTI -->
                    <div class="tab-pane fade" id="shareholders" role="tabpanel">
                        <h5 class="fw-bold mb-3">Azionisti</h5>

                        <!-- aggiungi nuovo azionista -->
                        <form action="<?= base_url('admin/CompanyManagementController/addShareholder') ?>" method="post"
                            class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold mb-1">Fondo / società</label>
                                <select name="firm_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>— Seleziona —</option>
                                    <?php foreach ($all_firms as $firm): ?>
                                        <!-- non mostra firm già azioniste -->
                                        <?php if (in_array((int) $firm['firm_id'], array_map('intval', $shareholderFirmIds), true)) {
                                            continue;
                                        } ?>
                                        <option value="<?= esc($firm['firm_id']) ?>"><?= esc($firm['firm_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold mb-1">Quota %</label>
                                <input type="number" name="ownership" class="form-control form-control-sm" step="0.01"
                                    min="0" max="100" required placeholder="0,00">
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i
                                        class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>
                        <!-- shareholders esistenti -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60%;">Fondo / società</th>
                                        <th class="text-end">Quota % e azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($shareholders)): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">Nessun azionista registrato.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <!-- tabella con azionisti modificabili -->
                                        <?php foreach ($shareholders as $sh): ?>
                                            <tr>
                                                <td class="fw-semibold text-dark"><?= esc($sh['firm_name']) ?></td>

                                                <td class="d-flex justify-content-end">
                                                    <form
                                                        action="<?= base_url('admin/CompanyManagementController/updateShareholder') ?>"
                                                        method="post" class="d-flex gap-2 align-items-center mb-0">
                                                        <!-- campi nascosti da passare a controller -->
                                                        <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                                                        <input type="hidden" name="firm_id" value="<?= esc($sh['firm_id']) ?>">

                                                        <div class="input-group input-group-sm" style="width: 100px;">
                                                            <input type="number" name="ownership" class="form-control"
                                                                step="0.01" min="0" max="100"
                                                                value="<?= esc($sh['ownership']) ?>" required>
                                                            <span class="input-group-text">%</span>
                                                        </div>

                                                        <div class="btn-group">
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                title="Salva quota">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                            <a href="<?= base_url('admin/CompanyManagementController/deleteShareholder/' . rawurlencode((string) $sh['firm_id']) . '/' . rawurlencode($company['isin'])) ?>"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Rimuovere azionista?')"
                                                                title="Elimina">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- FINE AZIONISTI -->

                    <!-- INIZIO CONSENSUS -->
                    <div class="tab-pane fade" id="consensus" role="tabpanel">
                        <h5 class="fw-bold mb-3">Consensus analisti</h5>
                        <!-- aggiunta nuovo consensus -->
                        <form action="<?= base_url('admin/CompanyManagementController/addConsensus') ?>" method="post"
                            class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                            <!-- isin da passare a controller -->
                            <input type="hidden" name="isin" value="<?= esc($company['isin']) ?>">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold mb-1">Banca d'Affari</label>
                                <select name="firm_id" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>— Seleziona —</option>
                                    <!-- firms -->
                                    <?php foreach ($all_firms as $firm): ?>
                                        <option value="<?= esc($firm['firm_id']) ?>"><?= esc($firm['firm_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold mb-1">Data</label>
                                <input type="date" name="date" class="form-control form-control-sm" required
                                    value="<?= esc(date('Y-m-d')) ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold mb-1">Rating</label>
                                <select name="rating_id" class="form-select form-select-sm" required>
                                    <!-- ratings -->
                                    <?php foreach (($ratings ?? []) as $r): ?>
                                        <option value="<?= esc($r['rating_id']) ?>"><?= esc($r['rating']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold mb-1">Prezzo obiettivo</label>
                                <input type="number" name="target_price" class="form-control form-control-sm"
                                    step="0.01" min="0" placeholder="opz.">
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i
                                        class="fas fa-plus me-1"></i>Aggiungi</button>
                            </div>
                        </form>
                        <!-- elenco consensus società -->
                        <div class="table-responsive">
                            <table class="table table-sm align-middle border mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Banca d'Affari</th>
                                        <th style="width: 50%;">Dettagli Analisi (Data / Rating / Target)</th>
                                        <th class="text-end" style="width: 10%;">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($consensus)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Nessun consensus registrato.
                                            </td>
                                        </tr>
                                    <?php else: ?>

                                        <?php foreach ($consensus as $row): ?>
                                            <tr>
                                                <td class="fw-semibold text-dark"><?= esc($row['firm_name']) ?></td>
                                                <!--consenus con possibilita di coancellazione e modifica  -->
                                                <td>
                                                    <form
                                                        action="<?= base_url('admin/CompanyManagementController/updateConsensus') ?>"
                                                        method="post" class="d-flex flex-wrap gap-2 align-items-center mb-0">

                                                        <input type="hidden" name="analysis_id"
                                                            value="<?= esc($row['analysis_id']) ?>">

                                                        <div class="flex-grow-1" style="min-width: 10rem;">
                                                            <input type="date" name="date" class="form-control form-control-sm"
                                                                required value="<?= esc($row['date']) ?>">
                                                        </div>

                                                        <div class="flex-grow-1" style="min-width: 8rem;">
                                                            <select name="rating_id" class="form-select form-select-sm"
                                                                required>
                                                                <?php foreach (($ratings ?? []) as $r): ?>
                                                                    <option value="<?= esc($r['rating_id']) ?>" <?= (int) $row['rating_id'] === (int) $r['rating_id'] ? 'selected' : '' ?>><?= esc($r['rating']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="flex-grow-1" style="min-width: 8rem;">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" name="target_price" class="form-control"
                                                                    style="width:100%;" step="0.01" min="0"
                                                                    value="<?= esc($row['target_price'] ?? '') ?>"
                                                                    placeholder="Target">
                                                                <span class="input-group-text"></span>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <button type="submit" class="btn btn-sm btn-success" title="Salva">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>

                                                <td class="text-end">
                                                    <a href="<?= base_url('admin/CompanyManagementController/deleteConsensus/' . (int) $row['analysis_id']) ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Eliminare questo consensus?')" title="Elimina">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- FINE CONSENSUS -->

                </div>
            </div>
        </div>
    </div>
</div>