import renderPagination from '../control.js';

//stato globale ricerca e filtro borsa
let currentQuery = '';
let currentMic = '';

document.addEventListener('DOMContentLoaded', () => {
    loadListings();

    document.getElementById('searchInput')?.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadListings(1);
    });

    //filtro per borsa
    document.getElementById('filterExchange')?.addEventListener('change', (e) => {
        currentMic = e.target.value;
        loadListings(1);
    });

    //apertura modal con dati del listing selezionato (event delegation)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.buy-listing-btn');
        if (!btn) return;

        //popola i campi hidden del modal con ticker, mic e prezzo
        document.getElementById('buyTicker').value = btn.dataset.ticker;
        document.getElementById('buyMic').value = btn.dataset.mic;
        document.getElementById('buyUnitPrice').value = btn.dataset.price || 0;
        document.getElementById('buyModalTitle').textContent = `Negozia — ${btn.dataset.ticker}:${btn.dataset.mic}`;

        //resetta quantita e ricalcola il costo
        const qtyInput = document.getElementById('buyQuantity');
        if (qtyInput) qtyInput.value = 1;
        updateCostEstimate();

        //apri il modal bootstrap
        const modal = new bootstrap.Modal(document.getElementById('buyListingModal'));
        modal.show();
    });

    //aggiorna stima costo quando si cambia la quantita
    document.getElementById('buyQuantity')?.addEventListener('input', updateCostEstimate);

    //auto-refresh ogni 2 minuti per aggiornare i prezzi
    setInterval(() => loadListings(), 10000);
});

//calcola e mostra il costo stimato nel modal (prezzo × quantita)
const updateCostEstimate = () => {
    const price = parseFloat(document.getElementById('buyUnitPrice')?.value || 0);
    const qty = parseInt(document.getElementById('buyQuantity')?.value || 1);
    const total = price * qty;
    const costEl = document.getElementById('costValue');
    if (costEl) {
        costEl.textContent = eur(total);
    }
};

//costruisce url con parametri di ricerca e filtro borsa
const buildListingsUrl = (page = 1) => {
    let qs = `page=${page}`;
    if (currentMic) qs += `&mic=${encodeURIComponent(currentMic)}`;
    return `/ListingController/search/${encodeURIComponent(currentQuery)}?${qs}`;
};

//tiene traccia della pagina corrente per il refresh automatico
let lastPage = 1;

const loadListings = (page) => {
    if (page !== undefined) lastPage = page;
    fetch(buildListingsUrl(lastPage))
        .then(res => res.json())
        .then(data => {
            renderRows(data.listings);
            renderPagination(data.pagination, loadListings);
        })
        .catch(err => console.error(err));
};

const eur = (n) =>
    '€ ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const renderRows = (listings) => {
    const tbody = document.getElementById('listingsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const frag = document.createDocumentFragment();
    (listings || []).forEach(l => frag.appendChild(row(l)));
    tbody.appendChild(frag);
};

const row = (l) => {
    const t = document.getElementById('listingRowTemplate');
    const tr = t.content.cloneNode(true).querySelector('tr');

    tr.querySelector('[data-field="ticker"]').textContent = l.ticker || '';
    tr.querySelector('[data-field="isin"]').textContent = l.isin || '';
    tr.querySelector('[data-field="company_name"]').textContent = l.company_name || '—';
    tr.querySelector('[data-field="exchange_name"]').textContent = l.exchange_name || l.mic || '';

    //ultimo prezzo con colore
    const priceCell = tr.querySelector('[data-field="last_price"]');
    if (l.last_price != null) {
        priceCell.innerHTML = `<span class="fw-semibold text-primary">${eur(l.last_price)}</span>`;
    } else {
        priceCell.innerHTML = '<span class="text-muted">—</span>';
    }

    //pulsante compra con dati del listing (incluso prezzo per il calcolo del costo)
    const buyBtn = tr.querySelector('[data-field="buy_btn"]');
    buyBtn.dataset.ticker = l.ticker;
    buyBtn.dataset.mic = l.mic;
    buyBtn.dataset.price = l.last_price || 0;

    return tr;
};
