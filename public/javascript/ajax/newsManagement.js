import renderPagination from '../control.js';

//stato globale per la query di ricerca corrente
let currentQuery = '';

//inizializza la pagina: carica le news, attiva la ricerca e i listener sui bottoni
document.addEventListener('DOMContentLoaded', () => {
    loadNews();
    searchNews();
    bindEditButtons();
    bindDeleteButtons();
});

//collega l'input di ricerca: ad ogni carattere digitato ricarica la lista news
const searchNews = () => {
    const input = document.getElementById('searchInput');
    if (!input) return;
    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadNews(1, currentQuery);
    });
};

//carica la lista news da server (ajax) con paginazione.
//chiama l'endpoint search del controller admin e renderizza tabella + paginazione
const loadNews = (page = 1, query = '') => {
    fetch(`/admin/NewsManagementController/search/${encodeURIComponent(query)}?page=${page}`)
        .then((res) => res.json())
        .then((data) => {
            renderNewsRows(data.news);
            //renderPagination e importato da control.js, gestisce i link della paginazione
            renderPagination(data.pagination, (newPage) => {
                loadNews(newPage, currentQuery);
            });
        })
        .catch((err) => console.error('Errore caricamento news:', err));
};

//svuota il tbody della tabella e lo ripopola con le righe ricevute dal server.
//usa un documentFragment per aggiornare il DOM una sola volta (performance)
const renderNewsRows = (rows) => {
    const tbody = document.getElementById('newsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const fragment = document.createDocumentFragment();
    (rows || []).forEach((item) => fragment.appendChild(createNewsRow(item)));
    tbody.appendChild(fragment);
};

//crea una singola riga <tr> per la tabella news usando il template html.
//popola data, titolo, sottotitolo, loghi aziende e bottoni azione
const createNewsRow = (n) => {
    const template = document.getElementById('newsRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');

    //formatta la data in formato italiano (dd/mm/yyyy + orario)
    const d = new Date(n.date);
    tr.querySelector('[data-field="date_cell"]').innerHTML =
        `${d.toLocaleDateString('it-IT')}<br><span class="text-muted">${d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' })}</span>`;

    tr.querySelector('[data-field="headline"]').textContent = n.headline || '';
    tr.querySelector('[data-field="subtitle"]').textContent = n.subtitle || '';

    //gestione loghi aziende collegate: logos_raw e una stringa con loghi separati da |||
    //arrivata dal GROUP_CONCAT nella query del model
    const logosCell = tr.querySelector('[data-field="logos"]');
    logosCell.innerHTML = '';
    //wrap per sovrappore i loghi
    const wrap = document.createElement('div');
    wrap.className = 'd-flex align-items-center';
    const paths = (n.logos_raw || '').split('|||').filter(Boolean);
    if (paths.length === 0) {
        wrap.innerHTML = '<span class="text-muted small">—</span>';
    } else {
        //mostra max 4 loghi sovrapposti
        paths.slice(0, 4).forEach((p, i) => {
            const img = document.createElement('img');
            const src = p.trim();
            //gestisce perrorsi assoluti e relativi, se comincia con http lo usa cosi com'è altrimenti perocorso relativo sostituendo ./ con /
            img.src = src.startsWith('http') ? src : (src.startsWith('/') ? src : '/' + src.replace(/^\.\//, ''));
            img.alt = '';
            img.style.width = '32px';
            img.style.height = '32px';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
            img.style.border = '2px solid #fff';
            img.style.boxShadow = '0 0 4px rgba(0,0,0,0.2)';
            //dal secondo logo in poi sovrappone leggermente a sinistra
            if (i > 0) {
                img.style.marginLeft = '-12px';
                img.style.position = 'relative';
                img.style.zIndex = String(10 - i);
            }
            wrap.appendChild(img);
        });
    }
    logosCell.appendChild(wrap);

    tr.querySelector('[data-field="newspaper"]').textContent = n.newspaper || '—';

    //imposta il news_id sui bottoni modifica/elimina (usati dai listener delegati)
    const editBtn = tr.querySelector('[data-field="edit_btn"]');
    const delBtn = tr.querySelector('[data-field="del_btn"]');
    editBtn.dataset.newsId = n.news_id;//.dataset crea attributo data-news-id con valore id news usato poi dai listener per identificare news da mostrare/eliminare
    delBtn.dataset.newsId = n.news_id;

    return tr;
};

//listener per i bottoni "modifica": intercetta il click su qualsiasi
//bottone con classe .open-news-edit-btn (anche quelli generati dinamicamente).
//carica i dati completi della news dal server e popola il modal di modifica
const bindEditButtons = () => {
    //non su ogni bottone perche sono generati dinamicamente
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-news-edit-btn');
        if (!btn) return;
        const id = btn.dataset.newsId;
        //chiama l'endpoint detail che restituisce tutti i campi + body + isin collegati
        fetch('/admin/NewsManagementController/detail/' + id)
            .then((res) => {
                if (!res.ok) throw new Error('Errore server');
                return res.json();
            })
            .then((data) => {
                //popola i campi del form di modifica
                document.getElementById('edit_news_id').value = data.news.news_id;
                document.getElementById('edit_headline').value = data.news.headline || '';
                document.getElementById('edit_subtitle').value = data.news.subtitle || '';
                document.getElementById('edit_author').value = data.news.author || '';
                document.getElementById('edit_body').value = data.body || '';

                //popola quill con il contenuto html formattato.
                //quillEdit e la variabile globale definita in modalNewsEdit.php
                //creata da initQuillEditor() di quill.js
                if (typeof quillEdit !== 'undefined' && quillEdit) {
                    quillEdit.root.innerHTML = data.body || '';
                }

                //popola il select con lista delle testate
                const sel = document.getElementById('edit_newspaper_id');
                sel.innerHTML = '';
                (data.newspapers || []).forEach((np) => {
                    const opt = document.createElement('option');
                    opt.value = np.newspaper_id;
                    opt.textContent = np.newspaper;
                    if (String(np.newspaper_id) === String(data.news.newspaper_id)) opt.selected = true;
                    sel.appendChild(opt);
                });

                //popola i 3 select delle societa collegate con gli isin salvati
                const isins = data.linked_isins || [];
                document.getElementById('edit_isin1').value = isins[0] || '';
                document.getElementById('edit_isin2').value = isins[1] || '';
                document.getElementById('edit_isin3').value = isins[2] || '';

                //apre il modal di modifica
                const modal = new bootstrap.Modal(document.getElementById('modalModificaNews'));
                modal.show();
            })
            .catch((err) => {
                console.error(err);
                alert('Errore caricamento news');
            });
    });
};

//listener per i bottoni "elimina": imposta l'id nel form di conferma
//e apre il modal di eliminazione
const bindDeleteButtons = () => {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-news-delete-btn');
        if (!btn) return;
        document.getElementById('delete_news_id').value = btn.dataset.newsId;
        const modal = new bootstrap.Modal(document.getElementById('modalEliminaNews'));
        modal.show();
    });
};
