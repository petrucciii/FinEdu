<div id="content-wrapper">
    <?php //barra superiore della pagina progressi educativi ?>
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Progressi Educazione</h5>
        <a href="/admin/ModuleManagementController/" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Moduli
        </a>
    </div>

    <div class="main-content pt-0">
        <?php //vista sola lettura dei progressi utente ?>
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">Progressi utenti</h5>
                    <p class="text-muted small mb-0">Vista in sola lettura basata su completed_lessons e users.experience.</p>
                </div>
                <?php //ricerca utenti: filtro dinamico lato client, submit opzionale per ricerca su DB ?>
                <form method="get" action="/admin/ModuleManagementController/progress" class="input-group input-group-sm"
                    style="max-width: 320px;">
                    <input type="text" name="search" id="progressSearchInput" class="form-control" placeholder="Cerca utente..."
                        value="<?= esc($search, 'attr') ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Utente</th>
                                <th>Email</th>
                                <th>Livello</th>
                                <th>XP</th>
                                <th>Lezioni completate</th>
                                <th>Tentativi</th>
                                <th>Errori</th>
                                <th>Ultima attivit&agrave;</th>
                            </tr>
                        </thead>
                        <tbody id="educationProgressBody">
                            <?php //stato vuoto della tabella progressi ?>
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Nessun utente trovato.</td>
                                </tr>
                            <?php else: ?>
                                <tr id="progressNoResults" class="d-none">
                                    <td colspan="8" class="text-center text-muted py-4">Nessun utente trovato.</td>
                                </tr>
                            <?php endif; ?>

                            <?php //ogni riga mostra progressi calcolati da completed_lessons ?>
                            <?php $avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info']; ?>
                            <?php foreach ($rows as $index => $row): ?>
                                <?php
                                //percentuale calcolata sulle lezioni attive totali
                                $completed = (int) $row['completed_lessons'];
                                $percent = $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0;
                                $fullName = trim((string) $row['first_name'] . ' ' . (string) $row['last_name']);
                                $initials = strtoupper(substr((string) $row['first_name'], 0, 1) . substr((string) $row['last_name'], 0, 1));
                                $initials = $initials !== '' ? $initials : '?';
                                $avatarClass = $avatarColors[$index % count($avatarColors)];
                                $searchText = strtolower($fullName . ' ' . (string) $row['email'] . ' ' . (string) ($row['level'] ?? ''));
                                ?>
                                <tr class="progress-row" data-search="<?= esc($searchText, 'attr') ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="<?= esc($avatarClass) ?> text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width:38px; height:38px; font-weight:bold;">
                                                <?= esc($initials) ?>
                                            </div>
                                            <strong><?= esc($fullName) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= esc($row['email']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= esc($row['level'] ?? '-') ?></span></td>
                                    <td><span class="fw-bold text-primary"><?= (int) $row['experience'] ?></span></td>
                                    <td style="min-width: 190px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span><?= $completed ?> / <?= (int) $totalLessons ?></span>
                                            <span class="fw-bold"><?= $percent ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                        </div>
                                    </td>
                                    <td><?= (int) $row['total_attempts'] ?></td>
                                    <td><?= (int) $row['failed_attempts'] ?></td>
                                    <td class="small text-muted">
                                        <?= $row['last_activity'] ? date('d/m/Y H:i', strtotime($row['last_activity'])) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <?php //paginazione manuale coerente con il model progressbyusers ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Mostrando <?= min((int) $pager['perPage'], max(0, (int) $pager['total'] - (((int) $pager['currentPage'] - 1) * (int) $pager['perPage']))) ?>
                        di <?= (int) $pager['total'] ?>
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 shadow-sm rounded">
                            <?php
                            $current = (int) $pager['currentPage'];
                            $pageCount = (int) $pager['pageCount'];
                            $queryBase = $search !== '' ? '&search=' . urlencode($search) : '';
                            ?>
                            <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link border-0 px-3" href="?page=<?= max(1, $current - 1) ?><?= $queryBase ?>">
                                    <i class="fas fa-chevron-left small"></i>
                                </a>
                            </li>
                            <?php for ($p = max(1, $current - 2); $p <= min($pageCount, $current + 2); $p++): ?>
                                <li class="page-item <?= $p === $current ? 'active' : '' ?>">
                                    <a class="page-link border-0 px-3 fw-bold mx-1 rounded"
                                        href="?page=<?= (int) $p ?><?= $queryBase ?>"><?= (int) $p ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $current >= $pageCount ? 'disabled' : '' ?>">
                                <a class="page-link border-0 px-3" href="?page=<?= min($pageCount, $current + 1) ?><?= $queryBase ?>">
                                    <i class="fas fa-chevron-right small"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('progressSearchInput');
            const rows = Array.from(document.querySelectorAll('#educationProgressBody .progress-row'));
            const noResults = document.getElementById('progressNoResults');
            if (!input) return;

            const filterRows = () => {
                const q = input.value.trim().toLowerCase();
                let visible = 0;

                rows.forEach((tr) => {
                    const hay = tr.getAttribute('data-search') || '';
                    const match = !q || hay.includes(q);
                    tr.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                if (noResults) {
                    noResults.classList.toggle('d-none', visible > 0);
                }
            };

            input.addEventListener('input', filterRows);
            filterRows();
        });
    </script>
</div>
