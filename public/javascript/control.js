/*
 * Helper unico per la paginazione AJAX.
 *
 * Tutte le tabelle dinamiche (utenti, news, portafogli, ordini, progressi) ricevono dal
 * controller lo stesso oggetto pagination. Questo helper costruisce i link Bootstrap e
 * chiama onPageChange(page) invece di ricaricare la pagina, così ogni view decide da sola
 * quale endpoint richiamare mantenendo ricerca e filtri correnti.
 */
function renderPagination({ currentPage, pageCount, perPage, total }, onPageChange) {
    const container = document.getElementById('paginationContainer');
    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
    container.innerHTML = '';

    //sinistra: quanti elelementi mostrati
    const info = document.createElement('span');
    info.className = 'text-muted small';
    const showing = Math.min(perPage, total - (currentPage - 1) * perPage);
    info.textContent = `Mostrando ${showing} di ${total}`;

    //lista bootstrap per impaginazione
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0 shadow-sm rounded';

    //quante pagine prima e dopo quelle correnti (2).
    const surroundCount = 2;



    //se il currentPage+surrondPage è più grande di pageCount(pagine totali), il range finisce li
    //altrimenti finisce a currentPage+surrondPage
    //es. page:3, surround: 2, pageCount:4; 3+2= 5 > 4 quindi si ferma a pageCount
    const rangeEnd = Math.min(pageCount, currentPage + surroundCount);

    //se invece è piu piccolo di 1 (pagina iniziale), comincia da 1
    //altrimenti comincia da current-surround
    // 1-2 = -1 < 1 quindi comincia da 1 
    const rangeStart = Math.max(1, currentPage - surroundCount);

    //crea un item (bottone con numero di pagina) per la paginazione
    const createPageItem = ({ label, page, active = false, disabled = false, isIcon = false }) => {
        const li = document.createElement('li');
        //classi bootstrap
        li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');


        if (disabled) {
            //se disabilitato crea uno span
            li.innerHTML = `<span class="page-link border-0 px-3 text-muted">${label}</span>`;
        } else {
            const a = document.createElement('a');
            //se è abilitato crea un link, se è un icona aggiunge classi adatte
            a.className = 'page-link border-0 px-3' + (isIcon ? '' : ' fw-bold mx-1 rounded');
            a.href = '#';
            //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
            a.innerHTML = label;
            //prevent default per evitare che al click il link ricarichi la pagina
            a.addEventListener('click', (e) => { e.preventDefault(); onPageChange(page); });//fa load delle righe
            li.appendChild(a);
        }

        return li;
    };


    if (currentPage > 1) {
        //i bottoni per la pagina precendente e la prima pagina sono attivi se la currentPage è dopo al prima pagian
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-left small"></i>', page: 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', page: currentPage - 1, isIcon: true }));
    } else {
        //bottoni disabilitati se siamo già nella prima pagina
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', disabled: true }));
    }

    //le altre pagine dentro il range hanno tutti i bottoni attivi
    for (let i = rangeStart; i <= rangeEnd; i++) {
        ul.appendChild(createPageItem({ label: i, page: i, active: i === currentPage }));
    }


    if (currentPage < pageCount) {
        //next page e last page abilitati solo se ci troviamo prima dell'ultima pagina
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', page: currentPage + 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-right small"></i>', page: pageCount, isIcon: true }));
    } else {
        //next page disabiliatato se gia ci troviamo nell'ultima
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', disabled: true }));
    }

    nav.appendChild(ul);

    //render nel DOM
    container.appendChild(info);
    container.appendChild(nav);
}

export default renderPagination;
