<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex">
                    <h5 class="modal-title">Gestione: </h5>
                    <h5 class="modal-title" id="modalUserTitle"></h5>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <form action="" name="modalEditForm" method="post" class="mb-1">
                        <div class="input-group">
                            <div class="form-floating flex-grow-1">
                                <input type="text" class="form-control modalInputFirstName" id="floatingFirstName"
                                    name="new_value" required value="" placeholder="Nome">
                                <label for="floatingFirstName">Nome</label>
                            </div>
                            <button class="btn btn-outline-primary px-4" name="edit" value="first_name"
                                type="submit">Salva</button>
                        </div>
                    </form>

                    <form action="" name="modalEditForm" method="post" class="mb-1">
                        <div class="input-group">
                            <div class="form-floating flex-grow-1">
                                <input type="text" class="form-control modalInputLastName" id="floatingLastName"
                                    name="new_value" required value="" placeholder="Cognome">
                                <label for="floatingLastName">Cognome</label>
                            </div>
                            <button class="btn btn-outline-primary px-4" name="edit" value="last_name"
                                type="submit">Salva</button>
                        </div>
                    </form>

                    <form action="" class="mb-2" name="modalEditForm" method="post">
                        <div class="input-group">
                            <div class="form-floating flex-grow-1">
                                <input type="email" class="form-control modalInputEmail" id="floatingEmail"
                                    name="new_value" placeholder="name@example.com" value="" required>
                                <label for="floatingEmail">Email</label>
                            </div>
                            <button class="btn btn-outline-primary px-4" type="submit" name="edit"
                                value="email">Salva</button>
                        </div>
                    </form>

                    <div class="d-flex align-items-center mb-1">
                        <strong class="me-1">Registrato il: </strong>
                        <p class="mb-0 text-muted" id="modalCreatedAt"></p>
                    </div>

                    <div class="d-flex align-items-center mb-1">
                        <strong class="me-1">Livello: </strong>
                        <p class="mb-0" id="modalLevel"></p>
                    </div>

                    <div class="d-flex align-items-center mb-1">
                        <strong class="me-1">Portafogli: </strong>
                        <p class="mb-0">2</p>
                    </div>
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
                <div class="d-grid gap-2">
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"><i
                            class="fas fa-trash me-2"></i> Elimina
                        Definitivamente</button>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
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
                <p class="text-muted">L'azione è irreversibile. Digita la tua password per confermare.</p>
                <form action="" id="modalDeleteForm" method="post">
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