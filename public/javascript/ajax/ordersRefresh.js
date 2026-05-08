//auto-refresh degli ordini utente ogni 2 minuti
//aggiorna ultimo prezzo e p&l non realizzato senza ricaricare la pagina
document.addEventListener('DOMContentLoaded', () => {
    //aggiorno periodicamente i dati per mantenere la schermata allineata senza reload
    setInterval(refreshOrders, 120000);
});

const eur = (n) =>
    '\u20ac ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const refreshOrders = () => {
    //uso fetch asincrono per aggiornare solo i dati necessari e preservare lo stato pagina
    fetch('/PortfolioController/refreshOrders')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            data.orders.forEach(o => {
                const row = document.querySelector(`[data-order-id="${o.order_id}"]`);
                if (!row) return;

                const priceCell = row.querySelector('[data-field="last_price"]');
                if (priceCell && Number(o.status) === 1) {
                    priceCell.textContent = o.last_price != null ? eur(o.last_price) : '-';
                }

                const pnlCell = row.querySelector('[data-field="pnl"]');
                if (!pnlCell) return;

                if (Number(o.status) === 1 && o.unrealized !== null) {
                    row.dataset.pnl = String(o.unrealized);
                    const cls = o.unrealized >= 0 ? 'text-success' : 'text-danger';
                    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
                    pnlCell.innerHTML = `<span class="${cls} fw-semibold">${eur(o.unrealized)}</span><div class="small text-muted">non real.</div>`;
                } else if (o.realized !== null) {
                    row.dataset.pnl = String(o.realized);
                    const cls = o.realized >= 0 ? 'text-success' : 'text-danger';
                    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
                    pnlCell.innerHTML = `<span class="${cls} fw-semibold">${eur(o.realized)}</span><div class="small text-muted">realizzato</div>`;
                }
            });

            document.dispatchEvent(new CustomEvent('orders:refreshed'));
        })
        .catch(err => console.error(err));
};
