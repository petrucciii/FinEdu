import renderPagination from '../control.js';

//variabili di stato per ricerca e filtri
let currentQuery = '';
let currentUser = 'all';
let currentPortfolio = 'all';
let currentTicker = '';
let currentMic = '';
let currentStatus = 'all';
let currentDate = '';
let currentPnlMin = '';
let currentPnlMax = '';


document.addEventListener('DOMContentLoaded', () => {
    const path = new URLSearchParams(location.search);//URLSearchParams legge i parametri get dall'URL
    const portfolio = path.get('pf');
    if (portfolio) {
        const select = document.getElementById('filterPortfolio');
        if (select) {
            select.value = portfolio;
            currentPortfolio = portfolio;
        }
    }

    //carica utenti
    loadOrders();

    //gestione
    document.getElementById('searchInput')?.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadOrders(1);
    });
    document.getElementById('filterUser')?.addEventListener('change', (e) => {
        currentUser = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterPortfolio')?.addEventListener('change', (e) => {
        currentPortfolio = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterTicker')?.addEventListener('input', (e) => {
        currentTicker = e.target.value.trim();
        loadOrders(1);
    });
    document.getElementById('filterMic')?.addEventListener('change', (e) => {
        currentMic = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterStatus')?.addEventListener('change', (e) => {
        currentStatus = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterDate')?.addEventListener('change', (e) => {
        currentDate = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterPnlMin')?.addEventListener('input', (e) => {
        currentPnlMin = e.target.value.trim();
        loadOrders(1);
    });
    document.getElementById('filterPnlMax')?.addEventListener('input', (e) => {
        currentPnlMax = e.target.value.trim();
        loadOrders(1);
    });

    //auto-refresh ogni 2 minuti per aggiornare prezzi e p&l
    setInterval(() => loadOrders(), 120000);
});

const buildOrdersUrl = (page = 1) => {
    let qs = `page=${page}`;
    if (currentUser && currentUser !== 'all') qs += `&user_id=${encodeURIComponent(currentUser)}`;
    if (currentPortfolio && currentPortfolio !== 'all') qs += `&portfolio_id=${encodeURIComponent(currentPortfolio)}`;
    if (currentTicker) qs += `&ticker=${encodeURIComponent(currentTicker)}`;
    if (currentMic) qs += `&mic=${encodeURIComponent(currentMic)}`;
    if (currentStatus && currentStatus !== 'all') qs += `&status=${encodeURIComponent(currentStatus)}`;
    if (currentDate) qs += `&date_from=${encodeURIComponent(currentDate)}`;
    if (currentPnlMin !== '') qs += `&pnl_min=${encodeURIComponent(currentPnlMin)}`;
    if (currentPnlMax !== '') qs += `&pnl_max=${encodeURIComponent(currentPnlMax)}`;
    return `/admin/OrderManagementController/search/${encodeURIComponent(currentQuery)}?${qs}`;
};

//tiene traccia della pagina corrente per il refresh automatico
let lastPage = 1;

const loadOrders = (page) => {
    if (page !== undefined) lastPage = page;
    fetch(buildOrdersUrl(lastPage))
        .then((res) => res.json())
        .then((data) => {
            renderRows(data.orders);
            renderPagination(data.pagination, loadOrders);
        })
        .catch((err) => console.error(err));
};

const eur = (n) =>
    '€ ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const renderRows = (orders) => {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const frag = document.createDocumentFragment();
    (orders || []).forEach((o) => frag.appendChild(row(o)));
    tbody.appendChild(frag);
};

const row = (o) => {
    const t = document.getElementById('orderRowTemplate');
    const tr = t.content.cloneNode(true).querySelector('tr');
    const qty = Number(o.quantity);
    const buy = Number(o.buyPrice);
    const notional = qty * buy;

    tr.querySelector('[data-field="order_id"]').textContent = '#' + o.order_id;
    tr.querySelector('[data-field="date"]').textContent = new Date(o.date).toLocaleString('it-IT');
    const closedCell = tr.querySelector('[data-field="closed_at"]');
    if (closedCell) {
        closedCell.textContent = o.closed_at ? new Date(o.closed_at).toLocaleString('it-IT') : '—';
    }
    tr.querySelector('[data-field="user"]').textContent = `${o.first_name || ''} ${o.last_name || ''}`.trim();
    tr.querySelector('[data-field="portfolio"]').textContent = o.portfolio_name || '';
    tr.querySelector('[data-field="ticker_mic"]').innerHTML = `<span>${o.ticker}:${o.mic}</span>`;
    tr.querySelector('[data-field="qty"]').textContent = String(qty);
    tr.querySelector('[data-field="buy"]').textContent = eur(buy);
    tr.querySelector('[data-field="sell"]').textContent =
        o.sellPrice != null ? eur(o.sellPrice) : '—';
    tr.querySelector('[data-field="notional"]').textContent = eur(notional);

    const pnlCell = tr.querySelector('[data-field="pnl"]');
    if (o.realized_pnl != null && o.realized_pnl !== '') {
        const v = Number(o.realized_pnl);
        pnlCell.innerHTML = `<span class="${v >= 0 ? 'text-success' : 'text-danger'} fw-semibold">${eur(v)}</span>`;
    } else {
        pnlCell.textContent = '—';
    }

    const st = tr.querySelector('[data-field="status"]');
    if (Number(o.status) === 1) {
        st.innerHTML =
            '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-check-circle me-1"></i>Aperto</span>';
    } else {
        st.innerHTML =
            '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Chiuso</span>';
    }

    return tr;
};
