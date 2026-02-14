<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Accedi a FinEdu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="#" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required
                            placeholder="nome.cognome@email.com">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Ricordami
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="text-decoration-none me-auto">Password dimenticata?</a>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Registrazione -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Crea un Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="#" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Nome</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="Mario">
                        </div>
                        <div class="col-6 mb-3">
                            <label>Cognome</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Rossi">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required
                            placeholder="mario.rossi@email.com">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required
                            placeholder="Minimo 8 caratteri">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                        <label class="form-check-label" for="acceptTerms">
                            Accetto i <a href="#">termini e condizioni</a>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">Registrati</button>
                </div>
            </form>
        </div>
    </div>
</div>