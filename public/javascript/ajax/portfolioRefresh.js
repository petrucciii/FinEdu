//auto-refresh dei portafogli utente ogni 2 minuti
//aggiorna i campi calcolati (valore totale, p&l) nelle card senza ricaricare la pagina
document.addEventListener('DOMContentLoaded', () => {
    setInterval(refreshPortfolios, 120000);
});

const eur = (n) =>
    '€ ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const refreshPortfolios = () => {
    fetch('/PortfolioController/refreshPortfolios')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            data.portfolios.forEach(pf => {
                //trova la card del portafoglio tramite data attribute
                const card = document.querySelector(`[data-pf-id="${pf.portfolio_id}"]`);
                if (!card) return;

                //aggiorna valore totale
                const totalEl = card.querySelector('[data-field="total_value"]');
                if (totalEl) totalEl.textContent = eur(pf.total_value || 0);

                //aggiorna liquidita
                const liqEl = card.querySelector('[data-field="liquidity"]');
                if (liqEl) liqEl.textContent = eur(pf.liquidity || 0);

                //aggiorna investito
                const invEl = card.querySelector('[data-field="invested"]');
                if (invEl) invEl.textContent = eur(pf.invested || 0);

                //aggiorna p&l non realizzato con colore condizionale
                const pnlEl = card.querySelector('[data-field="unrealized_pnl"]');
                if (pnlEl) {
                    const unreal = Number(pf.unrealized_pnl || 0);
                    pnlEl.textContent = eur(unreal);
                    pnlEl.className = 'fw-bold ' + (unreal >= 0 ? 'text-success' : 'text-danger');
                }
            });
        })
        .catch(err => console.error(err));
};
