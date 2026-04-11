import renderPagination from '../control.js';

//stato globale ricerca e ordinamento
let currentQuery = '';
let currentOrder = '';
let orderType = 'ASC';

//colori alternati per avatar come in userManagement
const avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info'];

document.addEventListener('DOMContentLoaded', () => {
    loadPortfolios();
    document.getElementById('searchInput')?.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadPortfolios(1);
    });

    orderBy();

    //auto-refresh ogni 2 minuti per aggiornare i prezzi calcolati
    setInterval(() => loadPortfolios(), 120000);
});

//costruisce url con parametri di ricerca e ordinamento
const buildPortfoliosUrl = (page = 1) => {
    let url = `/admin/PortfolioManagementController/search/${encodeURIComponent(currentQuery)}?page=${page}`;
    if (currentOrder) {
        url += `&order=${encodeURIComponent(currentOrder)}&order_type=${encodeURIComponent(orderType)}`;
    }
    return url;
};

//variabile per tenere traccia della pagina corrente durante il refresh
let lastPage = 1;

const loadPortfolios = (page) => {
    if (page !== undefined) lastPage = page;
    fetch(buildPortfoliosUrl(lastPage))
        .then((res) => res.json())
        .then((data) => {
            renderRows(data.portfolios);
            renderPagination(data.pagination, loadPortfolios);
        })
        .catch((err) => console.error(err));
};

//gestione ordinamento colonne
const orderBy = () => {
    document.addEventListener('click', (e) => {
        const th = e.target.closest('a[data-order]');
        if (!th) return;

        const clickedOrder = th.dataset.order;

        if (currentOrder === clickedOrder) {
            orderType = orderType === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentOrder = clickedOrder;
            orderType = 'DESC';
        }

        //reset icone di tutti gli header non selezionati
        document.querySelectorAll('a[data-order]').forEach(header => {
            if (header.dataset.order !== currentOrder) {
                header.innerHTML = header.textContent + "<i class='fas fa-sort-amount-up ms-1'></i>";
            }
        });

        //aggiorna icona dell'header selezionato
        const icon = document.createElement('i');
        icon.className = orderType === 'ASC'
            ? 'fas fa-sort-amount-up ms-1'
            : 'fas fa-sort-amount-down ms-1';

        th.innerHTML = `${th.textContent} ${icon.outerHTML}`;

        loadPortfolios(1);
    });
};

const eur = (n) =>
    '€ ' +
    Number(n).toLocaleString('it-IT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

//crea elemento non letto da dom come userManagement    
const renderRows = (rows) => {
    const tbody = document.getElementById('portfoliosTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const frag = document.createDocumentFragment();
    (rows || []).forEach((p, index) => frag.appendChild(row(p, index)));
    tbody.appendChild(frag);
};

//costruisce riga con colori alternati per avatar
const row = (p, index) => {
    const t = document.getElementById('portfolioRowTemplate');
    const tr = t.content.cloneNode(true).querySelector('tr');
    const initials = ((p.first_name || '?')[0] || '') + ((p.last_name || '?')[0] || '');
    //colore ciclico basato sull'indice, come in userManagement
    const colorClass = avatarColors[index % avatarColors.length];

    tr.querySelector('[data-field="pid"]').textContent = String(p.portfolio_id);

    const userCell = tr.querySelector('[data-field="user_cell"]');
    userCell.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="${colorClass} text-white rounded-circle d-flex justify-content-center align-items-center me-2"
                style="width:32px;height:32px;font-size:12px;font-weight:bold;">${initials.toUpperCase()}</div>
            <div><strong>${p.first_name || ''} ${p.last_name || ''}</strong><div class="small text-muted">${p.email || ''}</div></div>
        </div>`;

    tr.querySelector('[data-field="pname"]').textContent = p.name || '';
    tr.querySelector('[data-field="init_liq"]').textContent = eur(p.inital_liquidity || 0);
    tr.querySelector('[data-field="liq"]').textContent = eur(p.liquidity || 0);
    tr.querySelector('[data-field="inv"]').textContent = eur(p.invested || 0);
    tr.querySelector('[data-field="mv"]').textContent = eur(p.market_value_open || 0);
    tr.querySelector('[data-field="total"]').textContent = eur(p.total_value || 0);

    const unreal = Number(p.unrealized_pnl || 0);
    const pct = p.unrealized_pct != null ? ` (${unreal >= 0 ? '+' : ''}${p.unrealized_pct}%)` : '';
    const unrealCell = tr.querySelector('[data-field="unreal"]');
    unrealCell.innerHTML = `<span class="${unreal >= 0 ? 'text-success' : 'text-danger'} fw-bold">${eur(unreal)}${pct}</span>`;

    const link = tr.querySelector('[data-field="orders_link"]');
    link.href = `/admin/OrderManagementController/?pf=${p.portfolio_id}`;

    return tr;
};
