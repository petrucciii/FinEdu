<?php
$profileUser = $user ?? [];
$firstName = $profileUser['first_name'] ?? session()->get('first_name') ?? '';
$lastName = $profileUser['last_name'] ?? session()->get('last_name') ?? '';
$displayName = trim($firstName . ' ' . $lastName);
$email = $profileUser['email'] ?? session()->get('email') ?? '';
$level = $profileUser['level'] ?? session()->get('level') ?? '-';
$experience = (int) ($profileUser['experience'] ?? session()->get('experience') ?? 0);
$modules = $modules ?? [];
$recentAttempts = $recentAttempts ?? [];
$overallPercent = (int) ($progressPercent ?? 0);
$completedLessons = (int) ($completedLessons ?? 0);
$totalLessons = (int) ($totalLessons ?? 0);
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="profile-card mb-4">
                <div class="profile-cover"></div>

                <div class="profile-info-overlay">
                    <div class="user-details-text">
                        <h3 class="fw-bold text-dark">
                            <?= esc($displayName) ?>
                        </h3>
                        <span class="user-email-pill">
                            <i class="far fa-envelope me-1"></i>
                            <?= esc($email) ?>
                        </span>
                    </div>
                </div>

                <ul class="nav nav-tabs border-0 px-4 mt-2" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="settings-tab" data-bs-toggle="tab"
                            data-bs-target="#settings" type="button" role="tab" aria-controls="settings"
                            aria-selected="true">Impostazioni</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity"
                            type="button" role="tab" aria-controls="activity" aria-selected="false">Attivit&agrave;</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="profileTabContent">
                <div class="tab-pane fade show active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Dati Personali</h5>
                                    <form action="/UserController/editColumn" method="post" class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="new_value" required
                                                value="<?= esc($firstName, 'attr') ?>">
                                            <button class="btn btn-outline-primary px-4" name="edit" value="first_name"
                                                type="submit">Salva</button>
                                        </div>
                                    </form>
                                    <form action="/UserController/editColumn" method="post" class="mb-3">
                                        <label class="form-label">Cognome</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="new_value" required
                                                value="<?= esc($lastName, 'attr') ?>">
                                            <button class="btn btn-outline-primary px-4" name="edit" value="last_name"
                                                type="submit">Salva</button>
                                        </div>
                                    </form>
                                    <form action="/UserController/editColumn" method="post">
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" name="new_value"
                                                value="<?= esc($email, 'attr') ?>">
                                            <button class="btn btn-outline-primary px-4" type="submit" name="edit"
                                                value="email">Salva</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Cambia Password</h5>
                                    <form action="/UserController/editPassword" method="post">
                                        <div class="mb-3">
                                            <label class="form-label">Password Attuale</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nuova Password</label>
                                                <input type="password" name="new_password" class="form-control"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Conferma Nuova</label>
                                                <input type="password" name="repeat_password" class="form-control"
                                                    required>
                                                <div id="validationPasswordFeedback" class="invalid-feedback">
                                                    Password non coincidono
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" name="password_change"
                                            class="btn btn-warning w-100 fw-bold">Aggiorna Password</button>
                                    </form>
                                </div>
                            </div>

                            <div class="card border-danger rounded-4">
                                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold text-danger mb-1">Eliminazione Account</h6>
                                        <p class="small text-muted mb-0">Rimuovi permanentemente il tuo profilo e i
                                            dati.</p>
                                    </div>
                                    <button class="btn btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal">Elimina</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-center">
                                <h6 class="text-muted text-uppercase small fw-bold">Esperienza</h6>
                                <h3 class="fw-bold text-primary"><?= $experience ?> XP</h3>
                                <div class="progress mt-3" style="height: 10px; border-radius: 5px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $overallPercent ?>%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Livello: <?= esc($level) ?> - <?= $overallPercent ?>% completato
                                </small>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="fw-bold mb-3">Riepilogo lezioni</h6>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Completate</span>
                                    <span class="fw-bold"><?= $completedLessons ?> / <?= $totalLessons ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?= $overallPercent ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3">Progressi moduli</h5>

                                    <?php if (empty($modules)): ?>
                                        <p class="text-muted mb-0">Nessun modulo disponibile.</p>
                                    <?php else: ?>
                                        <?php foreach ($modules as $module): ?>
                                            <?php
                                            $modulePercent = (int) ($module['progress_percent'] ?? 0);
                                            $status = (string) ($module['status'] ?? 'available');
                                            $barClass = 'bg-primary';
                                            if ($status === 'completed') {
                                                $barClass = 'bg-success';
                                            } elseif ($status === 'locked') {
                                                $barClass = 'bg-secondary';
                                            }
                                            ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span><?= esc($module['name'] ?? '') ?></span>
                                                    <span class="fw-bold"><?= $modulePercent ?>%</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar <?= esc($barClass) ?>"
                                                        style="width: <?= $modulePercent ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h5 class="fw-bold mb-4">Ultimi tentativi lezioni</h5>

                                <?php if (empty($recentAttempts)): ?>
                                    <p class="text-muted mb-0">Non hai ancora completato o tentato lezioni.</p>
                                <?php else: ?>
                                    <?php foreach ($recentAttempts as $attempt): ?>
                                        <?php $passed = (int) ($attempt['completed'] ?? 0) === 1; ?>
                                        <div class="lesson-item">
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <h6 class="mb-0 fw-bold"><?= esc($attempt['title'] ?? 'Lezione') ?></h6>
                                                <span
                                                    class="badge <?= $passed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                                    <?= $passed ? 'Passato' : 'Fallito' ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <?= esc($attempt['module_name'] ?? 'Modulo') ?> -
                                                Tentativo n. <?= (int) ($attempt['attempt'] ?? 1) ?> -
                                                <?= !empty($attempt['date']) ? esc(date('d/m/Y H:i', strtotime($attempt['date']))) : '-' ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-5">
                <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size: 3.5rem;"></i>
                <h4 class="fw-bold">Sei sicuro?</h4>
                <p class="text-muted">L'azione &egrave; irreversibile. Digita la tua password per confermare.</p>
                <form action="/UserController/delete" method="post">
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password attuale"
                        required>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-danger px-4">Elimina Definitivamente</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
