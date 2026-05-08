import renderPagination from '../control.js';

const appUrl = window.appUrl || ((path = '') => '/' + String(path).replace(/^\/+/, ''));

//variabili di stato per ricerca, filtri e ordinamento
let currentQuery = '';
let currentUser = 'all';
let currentPortfolio = 'all';
let currentTicker = '';
let currentMic = '';
let currentStatus = 'all';
let currentDateStart = '';
let currentDateEnd = '';
let currentPnlMin = '';
let currentPnlMax = '';
let currentSort = 'order_id';
let currentDir = 'DESC';
let lastPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    const path = new URLSearchParams(location.search);
    const portfolio = path.get('pf');
    if (portfolio) {
        const select = document.getElementById('filterPortfolio');
        if (select) {
            select.value = portfolio;
            currentPortfolio = portfolio;
        }
    }

    loadOrders(1);
    bindFilters();
    bindSortHeaders();

    //auto-refresh ogni 2 minuti per aggiornare la stessa vista filtrata
    setInterval(() => loadOrders(), 120000);
});

const bindFilters = () => {
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
    document.getElementById('filterDateStart')?.addEventListener('change', (e) => {
        currentDateStart = e.target.value;
        loadOrders(1);
    });
    document.getElementById('filterDateEnd')?.addEventListener('change', (e) => {
        currentDateEnd = e.target.value;
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
};

const bindSortHeaders = () => {
    document.querySelectorAll('.order-sort').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sort = btn.dataset.sort || 'order_id';
            if (currentSort === sort) {
                currentDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = sort;
                currentDir = 'ASC';
            }
            updateSortHeaders();
            loadOrders(1);
        });
    });
    updateSortHeaders();
};

const updateSortHeaders = () => {
    document.querySelectorAll('.order-sort').forEach((btn) => {
        const active = btn.dataset.sort === currentSort;
        btn.classList.toggle('fw-bold', active);
        const icon = active ? (currentDir === 'ASC' ? ' ^' : ' v') : '';
        btn.textContent = btn.textContent.replace(/\s[\^v]$/, '') + icon;
    });
};

const dateError = () => {
    const start = currentDateStart ? new Date(`${currentDateStart}T00:00:00`) : null;
    const end = currentDateEnd ? new Date(`${currentDateEnd}T00:00:00`) : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (start && start > today) return 'La data inizio ordine non puo essere futura.';
    if (end && end > today) return 'La data fine ordine non puo essere futura.';
    if (start && end && start > end) return 'La data inizio ordine non puo essere successiva alla data fine.';

    return '';
};

const showError = (message) => {
    const box = document.getElementById('ordersFilterAlert');
    if (!box) return;
    box.textContent = message || '';
    box.classList.toggle('d-none', !message);
};

const buildOrdersUrl = (page = 1) => {
    let qs = `page=${page}&sort=${encodeURIComponent(currentSort)}&dir=${encodeURIComponent(currentDir)}`;
    if (currentUser && currentUser !== 'all') qs += `&user_id=${encodeURIComponent(currentUser)}`;
    if (currentPortfolio && currentPortfolio !== 'all') qs += `&portfolio_id=${encodeURIComponent(currentPortfolio)}`;
    if (currentTicker) qs += `&ticker=${encodeURIComponent(currentTicker)}`;
    if (currentMic) qs += `&mic=${encodeURIComponent(currentMic)}`;
    if (currentStatus && currentStatus !== 'all') qs += `&status=${encodeURIComponent(currentStatus)}`;
    if (currentDateStart) qs += `&date_start=${encodeURIComponent(currentDateStart)}`;
    if (currentDateEnd) qs += `&date_end=${encodeURIComponent(currentDateEnd)}`;
    if (currentPnlMin !== '') qs += `&pnl_min=${encodeURIComponent(currentPnlMin)}`;
    if (currentPnlMax !== '') qs += `&pnl_max=${encodeURIComponent(currentPnlMax)}`;

    const searchPath = currentQuery ? `/search/${encodeURIComponent(currentQuery)}` : '/search';
    return appUrl(`admin/OrderManagementController${searchPath}?${qs}`);
};

const loadOrders = (page) => {
    if (page !== undefined) lastPage = page;

    const error = dateError();
    showError(error);
    if (error) return;

    //uso fetch asincrono per aggiornare solo i dati necessari e preservare lo stato pagina
    fetch(buildOrdersUrl(lastPage))
        .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || data.error) {
                showError(data.error || 'Errore nel caricamento ordini.');
                renderRows([]);
                renderPagination(data.pagination || { currentPage: 1, perPage: 15, total: 0, pageCount: 1 }, loadOrders);
                return;
            }

            showError('');
            renderRows(data.orders);
            renderPagination(data.pagination, loadOrders);
        })
        .catch((err) => console.error(err));
};

const eur = (n) =>
    '\u20ac ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const renderRows = (orders) => {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
    tbody.innerHTML = '';

    if (!orders || !orders.length) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="12" class="text-center text-muted py-4">Nessun ordine trovato.</td>';
        tbody.appendChild(tr);
        return;
    }

    const frag = document.createDocumentFragment();
    orders.forEach((o) => frag.appendChild(row(o)));
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
    tr.querySelector('[data-field="closed_at"]').textContent = o.closed_at ? new Date(o.closed_at).toLocaleString('it-IT') : '-';
    tr.querySelector('[data-field="user"]').textContent = `${o.first_name || ''} ${o.last_name || ''}`.trim();
    tr.querySelector('[data-field="portfolio"]').textContent = o.portfolio_name || '';
    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
    tr.querySelector('[data-field="ticker_mic"]').innerHTML = `<span>${o.ticker}:${o.mic}</span>`;
    tr.querySelector('[data-field="qty"]').textContent = String(qty);
    tr.querySelector('[data-field="buy"]').textContent = eur(buy);
    tr.querySelector('[data-field="sell"]').textContent = o.sellPrice != null ? eur(o.sellPrice) : '-';
    tr.querySelector('[data-field="notional"]').textContent = eur(notional);

    const pnlCell = tr.querySelector('[data-field="pnl"]');
    if (o.realized_pnl != null && o.realized_pnl !== '') {
        const v = Number(o.realized_pnl);
        //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
        pnlCell.innerHTML = `<span class="${v >= 0 ? 'text-success' : 'text-danger'} fw-semibold">${eur(v)}</span>`;
    } else {
        pnlCell.textContent = '-';
    }

    const st = tr.querySelector('[data-field="status"]');
    if (Number(o.status) === 1) {
        //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
        st.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-check-circle me-1"></i>Aperto</span>';
    } else {
        //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
        st.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Chiuso</span>';
    }

    return tr;
};
