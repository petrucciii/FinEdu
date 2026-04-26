import renderPagination from '../control.js';

/*
 * Ricerca dinamica per "Progressi utenti".
 *
 * È volutamente strutturata come newsManagement.js: un input aggiorna currentQuery,
 * loadProgress chiama il controller, renderRows ricostruisce la tabella e
 * renderPagination gestisce i cambi pagina senza ricaricare la view.
 */
let currentQuery = '';
const selectedUserId = document.getElementById('content-wrapper')?.dataset.selectedUserId || '';
const avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info'];

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('progressSearchInput');
    currentQuery = input?.value.trim() || '';

    loadProgress(1, currentQuery);

    if (input) {
        input.addEventListener('input', (e) => {
            currentQuery = e.target.value.trim();
            loadProgress(1, currentQuery);
        });
    }
});

//costruisce l'URL evitando il segmento vuoto quando non c'è ricerca
const progressUrl = (page = 1, query = '') => {
    const searchPath = query ? `/progressSearch/${encodeURIComponent(query)}` : '/progressSearch';
    let url = `/admin/ModuleManagementController${searchPath}?page=${page}`;
    if (selectedUserId) {
        url += `&user_id=${encodeURIComponent(selectedUserId)}`;
    }
    return url;
};

//chiede i progressi al controller e aggiorna tabella + paginazione
const loadProgress = (page = 1, query = '') => {
    //uso fetch asincrono per aggiornare solo i dati necessari e preservare lo stato della pagina
    fetch(progressUrl(page, query))
        .then((res) => res.json())
        //se la risposta va a buon fine aggiorno la ui con i dati appena ricevuti
        .then((data) => {
            renderRows(data.rows || [], Number(data.totalLessons || 0));
            renderPagination(data.pagination, (newPage) => loadProgress(newPage, currentQuery));
        })
        .catch((err) => console.error('Errore caricamento progressi:', err));
};

//ricostruisce il tbody con un fragment per non aggiornare il DOM riga per riga
const renderRows = (rows, totalLessons) => {
    const tbody = document.getElementById('educationProgressBody');
    if (!tbody) return;
    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
    tbody.innerHTML = '';

    if (!rows.length) {
        const tr = document.createElement('tr');
        //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
        tr.innerHTML = '<td colspan="8" class="text-center text-muted py-4">Nessun utente trovato.</td>';
        tbody.appendChild(tr);
        return;
    }

    const fragment = document.createDocumentFragment();
    rows.forEach((row, index) => fragment.appendChild(createProgressRow(row, index, totalLessons)));
    tbody.appendChild(fragment);
};

//crea una riga progressi partendo dal template della view
const createProgressRow = (row, index, totalLessons) => {
    const template = document.getElementById('educationProgressRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');
    const completed = Number(row.completed_lessons || 0);
    const percent = totalLessons > 0 ? Math.round((completed / totalLessons) * 100) : 0;
    const firstName = row.first_name || '';
    const lastName = row.last_name || '';
    const fullName = `${firstName} ${lastName}`.trim() || '-';

    /*
     * Colore sequenziale come nelle altre view admin: dipende dalla posizione della riga,
     * non dal ruolo, così la lista resta visivamente coerente anche dopo una ricerca.
     */
    const avatar = tr.querySelector('[data-field="avatar"]');
    avatar.classList.add(avatarColors[index % avatarColors.length]);
    avatar.textContent = getInitials(firstName, lastName);

    tr.querySelector('[data-field="full_name"]').textContent = fullName;
    tr.querySelector('[data-field="email"]').textContent = row.email || '';
    tr.querySelector('[data-field="level"]').textContent = row.level || '-';
    tr.querySelector('[data-field="experience"]').textContent = Number(row.experience || 0);
    tr.querySelector('[data-field="completed_text"]').textContent = `${completed} / ${totalLessons}`;
    tr.querySelector('[data-field="percent_text"]').textContent = `${percent}%`;
    tr.querySelector('[data-field="progress_bar"]').style.width = `${percent}%`;
    tr.querySelector('[data-field="total_attempts"]').textContent = Number(row.total_attempts || 0);
    tr.querySelector('[data-field="failed_attempts"]').textContent = Number(row.failed_attempts || 0);
    tr.querySelector('[data-field="last_activity"]').textContent = row.last_activity ? formatDateTime(row.last_activity) : '-';

    return tr;
};

const getInitials = (first, last) =>
    (((first || '?')[0] || '') + ((last || '?')[0] || '')).toUpperCase();

const formatDateTime = (dateString) =>
    new Date(dateString).toLocaleString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
