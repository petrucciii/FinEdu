<div class="modal fade" id="modalEliminaNews" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Elimina news</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/NewsManagementController/delete') ?>" method="post" id="formDeleteNews">
                <div class="modal-body">
                    <input type="hidden" name="news_id" id="delete_news_id" value="">
                    <p class="mb-0">Confermi l'eliminazione di questa notizia? Non sarà più visibile sul sito.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">Elimina</button>
                </div>
            </form>
        </div>
    </div>
</div>