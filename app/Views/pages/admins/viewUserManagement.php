<div id="content-wrapper">
    <div class="top-bar d-flex justify-content-between align-items-center">
        <h5 class="m-0 text-muted">Gestione Utenti</h5>
        <div>
            <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                Esporta CSV <i class="fas fa-download"></i>
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card card-dashboard">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Database Utenti</h5>
                <div class="d-flex gap-2">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" placeholder="Cerca email o nome...">
                        <button class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filtra
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Livello: Principiante</a></li>
                            <li><a class="dropdown-item" href="#">Livello: Intermedio</a></li>
                            <li><a class="dropdown-item" href="#">Livello: Avanzato</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Utente</th>
                            <th>Email</th>
                            <th>Livello Educazione</th>
                            <th>Portafogli</th>
                            <th>Data Registrazione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['user_id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2"
                                            style="width:35px; height:35px; font-size: 14px;">
                                            <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                                        </div>
                                        <strong><?= $user['first_name'] . ' ' . $user['last_name'] ?></strong>
                                    </div>
                                </td>
                                <td><?= $user['email'] ?></td>
                                <td><span class="badge bg-info text-dark">Livello
                                        <?= $user['level'] ?>
                                    </span></td>
                                <td>2</td>
                                <td class="small text-muted">
                                    <?= $user['created_at'] ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                        data-bs-target="#userModal1">Gestisci</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Visualizzati 5 di 1,250 utenti</small>
                    <?= $pager->links() ?>

                </div>
            </div>
        </div>

        <!-- Modal Gestione Utente -->
        <div class="modal fade" id="userModal1" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestione: Mario Rossi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="text-muted">Informazioni Utente</h6>
                            <p class="mb-1"><strong>Email:</strong> mario.rossi@email.it</p>
                            <p class="mb-1"><strong>Registrato il:</strong> 15/01/2026</p>
                            <p class="mb-1"><strong>Livello:</strong> 3 - Intermedio</p>
                            <p class="mb-1"><strong>Portafogli:</strong> 2</p>
                            <p class="mb-1"><strong>Ultimo accesso:</strong> 12/02/2026 14:30</p>
                        </div>

                        <hr>

                        <h6 class="mb-3">Azioni Amministrative</h6>

                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary"><i class="fas fa-eye me-2"></i> Visualizza
                                Portafogli</button>
                            <button class="btn btn-outline-info"><i class="fas fa-chart-line me-2"></i> Vedi
                                Progressi Educativi</button>
                            <button class="btn btn-warning"><i class="fas fa-key me-2"></i> Invia Reset
                                Password</button>
                        </div>

                        <hr>

                        <p class="text-danger mb-2"><strong>Area Pericolo:</strong></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-danger"><i class="fas fa-ban me-2"></i> Ban
                                Utente</button>
                            <button class="btn btn-danger"><i class="fas fa-trash me-2"></i> Elimina
                                Definitivamente</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>