//filtri e ordinamento client-side per storico ordini utente
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('ordersSearchInput');
    const status = document.getElementById('ordersStatusFilter');
    const sort = document.getElementById('ordersSortSelect');
    const reset = document.getElementById('ordersResetFilters');

    search?.addEventListener('input', applyOrderControls);
    status?.addEventListener('change', applyOrderControls);
    sort?.addEventListener('change', applyOrderControls);

    reset?.addEventListener('click', () => {
        if (search) search.value = '';
        if (status) status.value = 'all';
        if (sort) sort.value = 'date_open_desc';
        applyOrderControls();
    });

    document.addEventListener('orders:refreshed', applyOrderControls);
    applyOrderControls();
});

const numeric = (row, name, fallback = 0) => {
    const value = row.dataset[name];
    if (value === undefined || value === null || value === '') return fallback;
    return Number(value);
};

const compareRows = (mode) => {
    const [field, dir] = mode.split(/_(?=[^_]+$)/);
    const direction = dir === 'asc' ? 1 : -1;

    return (a, b) => {
        let av = 0;
        let bv = 0;

        if (field === 'date_open') {
            av = numeric(a, 'dateOpen');
            bv = numeric(b, 'dateOpen');
        } else if (field === 'date_close') {
            av = numeric(a, 'dateClose');
            bv = numeric(b, 'dateClose');
        } else if (field === 'quantity') {
            av = numeric(a, 'quantity');
            bv = numeric(b, 'quantity');
        } else {
            //p&l assente viene mandato in fondo in entrambi gli ordinamenti
            av = numeric(a, 'pnl', dir === 'asc' ? Number.POSITIVE_INFINITY : Number.NEGATIVE_INFINITY);
            bv = numeric(b, 'pnl', dir === 'asc' ? Number.POSITIVE_INFINITY : Number.NEGATIVE_INFINITY);
        }

        return (av - bv) * direction;
    };
};

const applyOrderControls = () => {
    const tbody = document.getElementById('ordersBody');
    if (!tbody) return;

    const searchValue = document.getElementById('ordersSearchInput')?.value.trim().toLowerCase() || '';
    const statusValue = document.getElementById('ordersStatusFilter')?.value || 'all';
    const sortValue = document.getElementById('ordersSortSelect')?.value || 'date_open_desc';
    const emptyRow = document.getElementById('ordersEmptyFilterRow');
    const rows = Array.from(tbody.querySelectorAll('tr[data-order-row]'));

    rows.sort(compareRows(sortValue));
    rows.forEach((row) => tbody.insertBefore(row, emptyRow || null));

    let visible = 0;
    rows.forEach((row) => {
        const matchesSearch = !searchValue || (row.dataset.search || '').includes(searchValue);
        const matchesStatus = statusValue === 'all' || row.dataset.status === statusValue;
        const show = matchesSearch && matchesStatus;
        row.classList.toggle('d-none', !show);
        if (show) visible++;
    });

    if (emptyRow) {
        emptyRow.classList.toggle('d-none', visible > 0 || rows.length === 0);
    }
};
