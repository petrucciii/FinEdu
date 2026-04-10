<!-- Notizia vista da viewCompany -->
<div class="modal fade" id="companyNewsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- data-news-field vengono passati da viewCompany così da non dover usare AJAX -->
                <h6 class="text-muted" data-news-field="subtitle"></h6>
                <div class="mt-3 mb-3" data-news-field="body"></div>
                <small class="text-muted" data-news-field="meta"></small>
            </div>
        </div>
    </div>
</div>