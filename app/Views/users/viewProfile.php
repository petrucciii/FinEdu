<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="profile-card mb-4">
                <div class="profile-cover"></div>
                <div class="avatar-wrapper">
                    <div class="avatar-circle">MR</div>
                    <h3 class="fw-bold mt-2 mb-0">Mario Rossi</h3>
                    <p class="text-muted">mario.rossi@email.it</p>
                </div>

                <ul class="nav nav-tabs justify-content-center" id="profileTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                            data-bs-target="#overview" type="button">
                            <i class="fas fa-chart-line me-2"></i>Attività
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                            type="button">
                            <i class="fas fa-user-cog me-2"></i>Impostazioni
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="profileTabContent">

                <div class="tab-pane fade show active" id="overview">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-center">
                                <h6 class="text-muted text-uppercase small fw-bold">Esperienza</h6>
                                <h3 class="fw-bold text-primary">1,450 XP</h3>
                                <div class="progress mt-3" style="height: 10px; border-radius: 5px;">
                                    <div class="progress-bar" style="width: 65%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">Livello: Intermedio</small>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="fw-bold mb-3">Moduli</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Basi della Borsa</span>
                                        <span class="text-success fw-bold">100%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Analisi Bilancio</span>
                                        <span class="text-primary fw-bold">45%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" style="width: 45%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3">I miei Portafogli</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="p-3 border rounded-3">
                                                <small class="text-muted d-block">Lungo Termine</small>
                                                <span class="fw-bold">€ 5.240</span>
                                                <span class="text-success small ms-2">+4%</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 border rounded-3">
                                                <small class="text-muted d-block">Trading Test</small>
                                                <span class="fw-bold">€ 950</span>
                                                <span class="text-danger small ms-2">-1%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h5 class="fw-bold mb-4">Ultimi Tentativi Lezioni</h5>
                                <div class="lesson-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Analisi del Bilancio: Ricavi</h6>
                                        <span class="badge bg-success-subtle text-success">Passato</span>
                                    </div>
                                    <small class="text-muted">Tentativo n° 3 • 14/02/2026</small>
                                </div>
                                <div class="lesson-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Introduzione all'Inflazione</h6>
                                        <span class="badge bg-danger-subtle text-danger">Fallito</span>
                                    </div>
                                    <small class="text-muted">Tentativo n° 1 • 12/02/2026</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="settings">
                    <div class="row justify-content-center">
                        <div class="col-md-10">

                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Dati Personali</h5>
                                    <form class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="Mario">
                                            <button class="btn btn-outline-primary px-4" type="submit">Salva</button>
                                        </div>
                                    </form>
                                    <form class="mb-3">
                                        <label class="form-label">Cognome</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="Rossi">
                                            <button class="btn btn-outline-primary px-4" type="submit">Salva</button>
                                        </div>
                                    </form>
                                    <form>
                                        <label class="form-label">Email</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" value="mario.rossi@email.it">
                                            <button class="btn btn-outline-primary px-4" type="submit">Salva</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4">Cambia Password</h5>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">Password Attuale</label>
                                            <input type="password" class="form-control">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nuova Password</label>
                                                <input type="password" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Conferma Nuova</label>
                                                <input type="password" class="form-control">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning w-100 fw-bold">Aggiorna
                                            Password</button>
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
                <input type="password" class="form-control mb-3" placeholder="Password attuale">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger px-4">Elimina Definitivamente</button>
                </div>
            </div>
        </div>
    </div>
</div>