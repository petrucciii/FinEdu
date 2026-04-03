import renderPagination from '../control.js';

let currentQuery = '';

document.addEventListener('DOMContentLoaded', () => {
    loadNews();
    searchNews();
    bindEditButtons();
    bindDeleteButtons();
});

const searchNews = () => {
    const input = document.getElementById('searchInput');
    if (!input) return;
    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadNews(1, currentQuery);
    });
};

const loadNews = (page = 1, query = '') => {
    fetch(`/admin/NewsManagementController/search/${encodeURIComponent(query)}?page=${page}`)
        .then((res) => res.json())
        .then((data) => {
            renderNewsRows(data.news);
            renderPagination(data.pagination, (newPage) => {
                loadNews(newPage, currentQuery);
            });
        })
        .catch((err) => console.error('Errore caricamento news:', err));
};

const renderNewsRows = (rows) => {
    const tbody = document.getElementById('newsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const fragment = document.createDocumentFragment();
    (rows || []).forEach((item) => fragment.appendChild(createNewsRow(item)));
    tbody.appendChild(fragment);
};

const createNewsRow = (n) => {
    const template = document.getElementById('newsRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');

    const d = new Date(n.date);
    tr.querySelector('[data-field="date_cell"]').innerHTML =
        `${d.toLocaleDateString('it-IT')}<br><span class="text-muted">${d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' })}</span>`;

    tr.querySelector('[data-field="headline"]').textContent = n.headline || '';
    tr.querySelector('[data-field="subtitle"]').textContent = n.subtitle || '';

    const logosCell = tr.querySelector('[data-field="logos"]');
    logosCell.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'd-flex align-items-center';
    const paths = (n.logos_raw || '').split('|||').filter(Boolean);
    if (paths.length === 0) {
        wrap.innerHTML = '<span class="text-muted small">—</span>';
    } else {
        paths.slice(0, 4).forEach((p, i) => {
            const img = document.createElement('img');
            const src = p.trim();
            img.src = src.startsWith('http') ? src : (src.startsWith('/') ? src : '/' + src.replace(/^\.\//, ''));
            img.alt = '';
            img.style.width = '32px';
            img.style.height = '32px';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
            img.style.border = '2px solid #fff';
            img.style.boxShadow = '0 0 4px rgba(0,0,0,0.2)';
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

    const editBtn = tr.querySelector('[data-field="edit_btn"]');
    const delBtn = tr.querySelector('[data-field="del_btn"]');
    editBtn.dataset.newsId = n.news_id;
    delBtn.dataset.newsId = n.news_id;

    return tr;
};

const bindEditButtons = () => {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-news-edit-btn');
        if (!btn) return;
        const id = btn.dataset.newsId;
        fetch('/admin/NewsManagementController/detail/' + id)
            .then((res) => {
                if (!res.ok) throw new Error('Errore server');
                return res.json();
            })
            .then((data) => {
                document.getElementById('edit_news_id').value = data.news.news_id;
                document.getElementById('edit_headline').value = data.news.headline || '';
                document.getElementById('edit_subtitle').value = data.news.subtitle || '';
                document.getElementById('edit_author').value = data.news.author || '';
                document.getElementById('edit_body').value = data.body || '';

                // popola l'editor Quill con il contenuto HTML formattato
                if (typeof quillEdit !== 'undefined' && quillEdit) {
                    quillEdit.root.innerHTML = data.body || '';
                }

                const sel = document.getElementById('edit_newspaper_id');
                sel.innerHTML = '';
                (data.newspapers || []).forEach((np) => {
                    const opt = document.createElement('option');
                    opt.value = np.newspaper_id;
                    opt.textContent = np.newspaper;
                    if (String(np.newspaper_id) === String(data.news.newspaper_id)) opt.selected = true;
                    sel.appendChild(opt);
                });

                const isins = data.linked_isins || [];
                document.getElementById('edit_isin1').value = isins[0] || '';
                document.getElementById('edit_isin2').value = isins[1] || '';
                document.getElementById('edit_isin3').value = isins[2] || '';

                const modal = new bootstrap.Modal(document.getElementById('modalModificaNews'));
                modal.show();
            })
            .catch((err) => {
                console.error(err);
                alert('Errore caricamento news');
            });
    });
};

const bindDeleteButtons = () => {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-news-delete-btn');
        if (!btn) return;
        document.getElementById('delete_news_id').value = btn.dataset.newsId;
        const modal = new bootstrap.Modal(document.getElementById('modalEliminaNews'));
        modal.show();
    });
};
