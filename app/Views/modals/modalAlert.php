<?php
//toast bootstrap per notifiche invece del modal alert.
//il colore cambia in base al tipo: success = verde, danger = rosso, warning = giallo
$alert = session()->getFlashdata('alert');
$alert_type = session()->getFlashdata('alert_type') ?? 'warning';
?>
<?php if ($alert): ?>
    <!--contenitore fisso in alto a destra per i toast-->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="alertToast" class="toast align-items-center text-bg-<?= $alert_type ?> border-0 shadow-lg" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    <!--icona dinamica in base al tipo di alert-->
                    <?php if ($alert_type === 'success'): ?>
                        <i class="fas fa-check-circle me-2"></i>
                    <?php elseif ($alert_type === 'danger'): ?>
                        <i class="fas fa-times-circle me-2"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php endif; ?>
                    <?= $alert ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        //mostra il toast automaticamente al caricamento della pagina
        document.addEventListener('DOMContentLoaded', function () {
            var toast = new bootstrap.Toast(document.getElementById('alertToast'), { delay: 4000 });
            toast.show();
        });
    </script>
<?php endif; ?>