<div class="container mt-4 mb-5" style="min-height: 80vh;">
    <?php //mostra livello, xp e avanzamento complessivo dell'utente ?>
    <div class="row mb-4">
        <div class="col-md-7 mx-auto">
            <div class="p-3 text-center">
                <h5 class="mb-3">Livello <?= esc($user['level'] ?? 'Principiante') ?></h5>
                <div class="progress" role="progressbar" aria-valuenow="<?= (int) $progressPercent ?>" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                        style="width: <?= (int) $progressPercent ?>%"></div>
                </div>
                <p class="small text-muted mt-2 mb-0">
                    <?= (int) ($user['experience'] ?? 0) ?> XP maturati -
                    <?= (int) $completedLessons ?> / <?= (int) $totalLessons ?> lezioni completate
                </p>
            </div>
        </div>
    </div>

    <div class="text-center mb-5">
        <h2 class="fw-bold">Il tuo percorso di apprendimento</h2>
        <p class="lead text-muted">Migliora le tue competenze finanziarie modulo dopo modulo.</p>
    </div>

    <div class="container" style="max-width: 850px;">
        <?php //messaggio mostrato quando non ci sono moduli attivi nel db ?>
        <?php if (empty($modules)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-graduation-cap text-muted mb-3" style="font-size: 2rem;"></i>
                    <h5 class="fw-bold">Nessun modulo disponibile</h5>
                    <p class="text-muted mb-0">I contenuti formativi saranno pubblicati a breve.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php //lista dei moduli con stato progressivo calcolato dal controller ?>
        <?php foreach ($modules as $index => $module): ?>
            <?php
            //prepara icona, colore e bottone in base allo stato del modulo
            $status = $module['status'];
            $iconClass = 'fas fa-play-circle';
            $iconColor = 'text-primary';
            $buttonText = 'Inizia';
            $buttonClass = 'btn btn-primary rounded-pill px-4';
            $disabled = false;

            if ($status === 'completed') {
                $iconClass = 'fas fa-check-circle';
                $iconColor = 'text-success';
                $buttonText = 'Ripassa';
            } elseif ($status === 'in_progress') {
                $iconClass = 'fas fa-play-circle';
                $buttonText = 'Continua';
            } elseif ($status === 'locked') {
                $iconClass = 'fas fa-lock';
                $iconColor = 'text-secondary';
                $buttonText = 'Bloccato';
                $buttonClass = 'btn btn-light rounded-pill px-4';
                $disabled = true;
            } elseif ((int) $module['lesson_count'] === 0) {
                $buttonText = 'Vuoto';
                $buttonClass = 'btn btn-light rounded-pill px-4';
                $disabled = true;
            }
            ?>

            <?php //card del singolo modulo mantenendo lo stile del prototipo ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="me-3 fs-2 <?= esc($iconColor) ?>">
                            <i class="<?= esc($iconClass) ?>"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= esc($module['name']) ?></h5>
                            <small class="text-muted">
                                <?= (int) $module['lesson_count'] ?> lezioni -
                                <?= (int) $module['progress_percent'] ?>% completato
                            </small>
                        </div>
                    </div>

                    <?php //se il modulo e bloccato o vuoto non deve essere cliccabile ?>
                    <?php if ($disabled): ?>
                        <button class="<?= esc($buttonClass) ?>" disabled><?= esc($buttonText) ?></button>
                    <?php else: ?>
                        <a href="/EducationController/module/<?= (int) $module['id_module'] ?>"
                            class="<?= esc($buttonClass) ?>"><?= esc($buttonText) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($index < count($modules) - 1): ?>
                <div class="text-center my-2 text-muted"><i class="fas fa-ellipsis-v"></i></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
