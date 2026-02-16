<div id="content-wrapper">
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0 text-muted">Gestione Utenti</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" type="button"><i class="fas fa-download"></i> Esporta CSV</button>
            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal"><i
                    class="fas fa-plus"></i> Aggiungi Utente</button>
        </div>
    </div>

    <div class="main-content">
        <div class="card card-dashboard border-0 shadow-sm">
            <div class="card card-dashboard">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Database Utenti</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Cerca email o nome...">
                            <button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filtra
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Tutti gli utenti</a></li>
                                <li><a class="dropdown-item" href="#">Solo attivi</a></li>
                                <li><a class="dropdown-item" href="#">Solo bannati</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Livello: Principiante</a></li>
                                <li><a class="dropdown-item" href="#">Livello: Intermedio</a></li>
                                <li><a class="dropdown-item" href="#">Livello: Avanzato</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover w-100" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Utente</th>
                                    <th>Email</th>
                                    <th>Ruolo</th>
                                    <th>Livello Educazione</th>
                                    <th>Portafogli</th>
                                    <th>Data Registrazione</th>
                                    <th class="text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info'];
                                foreach ($users as $index => $user):
                                    $colorClass = $avatarColors[$index % count($avatarColors)];
                                    $is_admin = ($user['role'] === 'admin');
                                    $roleBadge = $is_admin ? 'bg-danger' : 'bg-secondary';
                                    $lvl = strtolower($user['level']);
                                    $lvlBadge = ($lvl == 'principiante') ? 'bg-success' : (($lvl == 'intermedio') ? 'bg-primary' : 'bg-warning text-dark');
                                    ?>
                                    <tr>
                                        <td class="text-muted">#<?= $user['user_id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="<?= $colorClass ?> text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                                                    style="width:38px; height:38px; font-weight: bold;">
                                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge <?= $roleBadge ?>"><?= ucfirst($user['role']) ?></span></td>
                                        <td><span class="badge <?= $lvlBadge ?>"><?= ucfirst($user['level']) ?></span></td>
                                        <td><span class="fw-bold"><?= $user['portfolios_count'] ?? 0 ?></span></td>
                                        <td class="small text-muted"><?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal"
                                                data-bs-target="#userModal<?= $user['user_id'] ?>">
                                                <i class="fas fa-cog"></i> Gestisci
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <?php
                                $start = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1;//first item number = (currentPage - 1) * itemsPerPage + 1 = 1 for page 1, 11 for page 2, etc.
                                $end = min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()); //last item number = currentPage * itemsPerPage, but cannot exceed total items
                                $total = $pager->getTotal();//total items
                                ?>
                                Utenti da <strong>
                                    <?= $start ?>
                                </strong> a <strong>
                                    <?= $end ?>
                                </strong> di <strong>
                                    <?= $total ?>
                                </strong>
                            </div>
                            <?php if ($pager): ?>
                                <?= $pager->links() ?>
                            <?php endif; ?>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>