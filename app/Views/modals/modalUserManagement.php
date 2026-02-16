<?php if ($user): ?>
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
                        <p class="mb-1"><strong>Email:</strong>
                            <?= $user['email'] ?>
                        </p>
                        <p class="mb-1"><strong>Registrato il:</strong>
                            <?= $user['created_at'] ?>
                        </p>
                        <p class="mb-1"><strong>Livello:</strong> 3 -
                            <?= $user['level'] ?>
                        </p>
                        <p class="mb-1"><strong>Portafogli:</strong> 2</p>
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

                    <p class="text-danger mb-2"><strong>Azioni Irreversibili</strong></p>

                    <button class="btn btn-danger gap-2"><i class="fas fa-trash me-2"></i> Elimina
                        Definitivamente</button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>