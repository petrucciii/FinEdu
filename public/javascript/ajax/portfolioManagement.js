import renderPagination from '../control.js';

/*
 * Gestione AJAX della pagina admin portafogli.
 *
 * La view contiene solo tabella, input e template riga. Questo script chiama il controller
 * per ottenere JSON gia filtrato/ordinato e poi ricostruisce il tbody. Se l'URL contiene
 * ?user_id=..., per esempio arrivando dal modal utenti, tutte le richieste mantengono
 * quel filtro e mostrano solo i portafogli dell'utente scelto.
 */

//stato globale ricerca e ordinamento
let currentQuery = '';
let currentOrder = '';
let orderType = 'ASC';
const selectedUserId = new URLSearchParams(window.location.search).get('user_id') || '';

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

/*
 * Costruisce URL dell'endpoint. Come per gli utenti, quando la ricerca e' vuota
 * non aggiungiamo uno slash con segmento vuoto; questo rende il routing piu' stabile.
 * user_id viene sempre riaggiunto alle chiamate AJAX per non perdere il filtro.
 */
const buildPortfoliosUrl = (page = 1) => {
    const searchPath = currentQuery ? `/search/${encodeURIComponent(currentQuery)}` : '/search';
    let url = `/admin/PortfolioManagementController${searchPath}?page=${page}`;
    if (selectedUserId) {
        url += `&user_id=${encodeURIComponent(selectedUserId)}`;
    }
    if (currentOrder) {
        url += `&order=${encodeURIComponent(currentOrder)}&order_type=${encodeURIComponent(orderType)}`;
    }
    return url;
};

//pagina corrente conservata anche durante l'auto-refresh dei prezzi
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

//gestione ordinamento colonne: alterna ASC/DESC e ricarica dalla prima pagina
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

//crea le righe in un fragment, cosi il DOM viene aggiornato una sola volta
const renderRows = (rows) => {
    const tbody = document.getElementById('portfoliosTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const frag = document.createDocumentFragment();
    (rows || []).forEach((p, index) => frag.appendChild(row(p, index)));
    tbody.appendChild(frag);
};

//costruisce una riga usando il template HTML della view e colori avatar sequenziali
const row = (p, index) => {
    const t = document.getElementById('portfolioRowTemplate');
    const tr = t.content.cloneNode(true).querySelector('tr');
    const initials = ((p.first_name || '?')[0] || '') + ((p.last_name || '?')[0] || '');
    //colore ciclico basato sull'indice, coerente con la gestione utenti
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

    /*
     * P&L e percentuale sono metriche calcolate dal model. Qui si decide solo come
     * mostrarle: verde se positive o zero, rosse se negative.
     */
    const unreal = Number(p.unrealized_pnl || 0);
    const pct = p.unrealized_pct != null ? ` (${unreal >= 0 ? '+' : ''}${p.unrealized_pct}%)` : '';
    const unrealCell = tr.querySelector('[data-field="unreal"]');
    unrealCell.innerHTML = `<span class="${unreal >= 0 ? 'text-success' : 'text-danger'} fw-bold">${eur(unreal)}${pct}</span>`;

    const link = tr.querySelector('[data-field="orders_link"]');
    link.href = `/admin/OrderManagementController/?pf=${p.portfolio_id}`;

    return tr;
};
