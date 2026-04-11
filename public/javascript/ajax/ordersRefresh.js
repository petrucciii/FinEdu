//auto-refresh degli ordini utente ogni 2 minuti
//aggiorna ultimo prezzo e p&l non realizzato per gli ordini aperti senza ricaricare la pagina
document.addEventListener('DOMContentLoaded', () => {
    setInterval(refreshOrders, 120000);
});

const eur = (n) =>
    '€ ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

//funzione per aggiornare prezzi degli ordini
const refreshOrders = () => {
    fetch('/PortfolioController/refreshOrders')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            data.orders.forEach(o => {
                //trova la riga dell'ordine tramite data attribute
                const row = document.querySelector(`[data-order-id="${o.order_id}"]`);
                if (!row) return;

                //aggiorna ultimo prezzo per ordini aperti
                const priceCell = row.querySelector('[data-field="last_price"]');
                if (priceCell && Number(o.status) === 1) {
                    priceCell.textContent = o.last_price != null ? eur(o.last_price) : '—';
                }

                //aggiorna p&l
                const pnlCell = row.querySelector('[data-field="pnl"]');
                if (pnlCell) {
                    if (Number(o.status) === 1 && o.unrealized !== null) {
                        //p&l non realizzato per ordini aperti
                        const cls = o.unrealized >= 0 ? 'text-success' : 'text-danger';
                        pnlCell.innerHTML = `<span class="${cls} fw-semibold">${eur(o.unrealized)}</span><div class="small text-muted">non real.</div>`;
                    } else if (o.realized !== null) {
                        //p&l realizzato per ordini chiusi
                        const cls = o.realized >= 0 ? 'text-success' : 'text-danger';
                        pnlCell.innerHTML = `<span class="${cls} fw-semibold">${eur(o.realized)}</span><div class="small text-muted">realizzato</div>`;
                    }
                }
            });
        })
        .catch(err => console.error(err));
};
