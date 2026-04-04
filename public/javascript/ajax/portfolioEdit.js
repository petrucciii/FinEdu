document.addEventListener('DOMContentLoaded', function () {
    // Gestione click sul pulsante modifica nome (event delegation)
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-name-btn');
        if (!editBtn) return;

        const container = editBtn.closest('.portfolio-name-container');
        const portfolioId = container.dataset.portfolioId;
        const nameSpan = container.querySelector('.portfolio-name');
        const originalName = nameSpan.textContent;

        // sostituisci con input e pulsante salva
        container.innerHTML = `
                <input type="text" class="form-control form-control-sm name-input" value="${originalName}" data-original-name="${originalName}">
                <button type="button" class="btn btn-link p-0 text-success border-0 shadow-none save-name-btn" data-portfolio-id="${portfolioId}">
                    <i class="fas fa-save"></i>
                </button>
                <button type="button" class="btn btn-link p-0 text-danger border-0 shadow-none discard-name-btn">
                    <i class="fas fa-times"></i>
                </button>
            `;
    });

    // Gestione eventi su elementi dinamici (save e discard) con event delegation
    document.addEventListener('click', function (e) {
        const saveBtn = e.target.closest('.save-name-btn');
        const discardBtn = e.target.closest('.discard-name-btn');

        if (saveBtn) {
            // Salva nome - usa fetch AJAX
            const container = saveBtn.closest('.portfolio-name-container');
            const portfolioId = container.dataset.portfolioId;
            const input = container.querySelector('.name-input');
            const newName = input.value.trim();

            if (!newName) {
                alert('Il nome non può essere vuoto');
                return;
            }

            const formData = new URLSearchParams();
            formData.append('portfolio_id', portfolioId);
            formData.append('name', newName);

            fetch('/PortfolioController/updatePortfolioName', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Aggiorna visualizzazione
                        container.innerHTML = `
                            <span class="portfolio-name">${newName}</span>
                            <button type="button" class="btn btn-link p-0 text-info border-0 shadow-none edit-name-btn">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        `;
                    } else {
                        alert(data.message || 'Errore nel salvataggio');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Errore di connessione');
                });
        }

        if (discardBtn) {
            // Annulla modifiche - ripristina visualizzazione originale
            const container = discardBtn.closest('.portfolio-name-container');
            const portfolioId = container.dataset.portfolioId;
            const input = container.querySelector('.name-input');
            const originalName = input.dataset.originalName;

            container.innerHTML = `
                    <span class="portfolio-name">${originalName}</span>
                    <button type="button" class="btn btn-link p-0 text-info border-0 shadow-none edit-name-btn">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                `;
        }
    });
});