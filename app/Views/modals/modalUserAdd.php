<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex">
                    <h5 class="modal-title">Aggiungi Utente</h5>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                 <form action="/admin/UserManagementController/add" method="post">
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
                    <div class="mb-3">
                        <?php
                        foreach ($roles as $role) :
                        ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="role_id" value="<?= intval($role['role_id']) ?>" checked=<?= intval($role['role_id']) == 2 ? true : false ?>>
                                <label class="form-check-label" for="role_id"><?= ucfirst($role['role']) ?></label>
                            </div>
                            <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    
                    <button type="submit" class="btn btn-success w-100">Aggiungi</button>
                </div>
            </form>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>