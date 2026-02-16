<?php $alert = session()->getFlashdata('alert'); ?>
<?php if ($alert): ?>

    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-white border-0">
                    <h5 class="modal-title fw-bold" id="alertModalLabel">
                        <i class="fas fa-exclamation text-warning me-2"></i>Attenzione!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-circle text-warning" style="font-size: 3.5rem;"></i>
                    </div>
                    <p class="fs-5 text-dark mb-0">
                        <?= $alert ?>

                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-warning px-4 rounded-pill" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        //executed just in case there is an alert inside the session
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('alertModal'));
            myModal.show();
        });
    </script>
<?php endif; ?>