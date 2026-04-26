import renderPagination from '../control.js';

/*
 * Gestione AJAX della pagina admin utenti.
 *
 * Il PHP renderizza la struttura della tabella e il <template>; questo file chiede
 * al controller i dati JSON e ricostruisce righe, paginazione, filtri e ordinamenti.
 * Cosi la ricerca resta dinamica e non serve ricaricare tutta la pagina.
 */
document.addEventListener('DOMContentLoaded', () => {
    settingsModal();
    loadUsers();//carica gli utenti quando pagina è caricata (piccolo delay)
    searchUser();
    filterByLevel();
    filterByRole();
    orderBy();
    exportUsers();
});

//finestra modale per gestione utente: un solo modal viene riempito via AJAX al click
const settingsModal = () => {
    //event delegation: funziona anche sulle righe ricreate dopo ricerca o cambio pagina
    document.addEventListener('click', (e) => {

        let btn = e.target.closest('.open-user-btn');
        if (!btn) return;

        //prende id da data-attribute
        let userId = btn.dataset.id;

        //fetch ajax all'endpoint con userId, evitando dati precaricati e modal duplicati
        fetch('/admin/UserManagementController/settings/' + userId)//endpoint : UserManagement::settings($userId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore server');
                }

                return response.json();
            })
            .then(data => {
                //popola modal con i dati correnti dell'utente selezionato
                document.getElementById('modalUserTitle').textContent = " Utente #" + data.user.user_id;
                document.getElementsByClassName('modalInputFirstName')[0].value = data.user.first_name;
                document.getElementsByClassName('modalInputLastName')[0].value = data.user.last_name;
                document.getElementsByClassName('modalInputEmail')[0].value = data.user.email;
                document.getElementById('modalCreatedAt').textContent = new Date(data.user.created_at).toLocaleDateString();
                document.getElementById('modalLevel').textContent = data.user.level || '-';
                document.getElementById('modalPortfolioCount').textContent = Number(data.user.portfolio_count || 0);
                /*
                 * I bottoni non aprono tab dentro il modal: portano alle view admin gia
                 * esistenti e passano user_id. Quelle view filtrano progressi o portafogli
                 * lato server, quindi si vede subito il dato dell'utente cliccato.
                 */
                document.getElementById('modalPortfolioLink').href = `/admin/PortfolioManagementController/?user_id=${encodeURIComponent(data.user.user_id)}`;
                document.getElementById('modalProgressLink').href = `/admin/ModuleManagementController/progress?user_id=${encodeURIComponent(data.user.user_id)}`;

                //inseisce attributi per i form (bottoni) con le varie azioni
                document.querySelectorAll('form[name="modalEditForm"]').forEach(form => {
                    form.action = '/admin/UserManagementController/editColumn/' + userId;
                });
                document.getElementById('modalDeleteForm').action = '/admin/UserManagementController/delete/' + userId;

                //radio buttons per i ruoli: generati dai dati DB, non scritti fissi nel JS
                let rolesContainer = document.getElementById('modalRolesContainer');
                //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
                rolesContainer.innerHTML = "";//pulisce il container per evitare duplicazioni

                //mette dentro il container ogni ruolo e seleziona quello corrente
                data.roles.forEach(role => {
                    let div = document.createElement('div');
                    div.className = "form-check form-check-inline";
                    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
                    div.innerHTML = `
                    <input class="form-check-input" type="radio" name="new_value" id="inlineRadio${role['role_id']}"
                        value=${role['role_id']}>
                    <label class="form-check-label" for="inlineRadio${role['role_id']}">${role['role'][0].toUpperCase() + role['role'].slice(1)}</label>
                `;
                    if (role['role_id'] == data.user.role_id) {
                        div.querySelector('input').checked = true;
                    }
                    rolesContainer.appendChild(div);
                });;

                //apre il modal
                let modal = new bootstrap.Modal(
                    document.getElementById('userModal')
                );

                modal.show();


            })
            .catch(err => {
                console.error(err);
                alert("Errore caricamento utente");
            });
    });
}

//colori in sequenza per avatar
const avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info'];

//variabili di stato per ricerca e filtri
let currentQuery = '';
let currentRole = '';
let currentLevel = '';
let currentOrder = '';
let orderType = 'ASC';

/*
 * Costruisce l'URL dell'endpoint utenti partendo dallo stato corrente.
 *
 * Quando la ricerca e' vuota usiamo /search senza segmento aggiuntivo: /search/
 * con parametro vuoto puo' essere ambiguo in alcuni setup di routing. La ricerca
 * viene poi divisa in parole nel model, cosi lo stesso input cerca nome, cognome
 * ed email anche con query come "rossi mario".
 */
const buildUsersUrl = (page = 1, exportCsv = false) => {
    //parametri get
    let queryString = `page=${page}`;

    //filtri
    if (currentRole) queryString += `&role_id=${encodeURIComponent(currentRole)}`;
    if (currentLevel) queryString += `&level_id=${encodeURIComponent(currentLevel)}`;
    if (currentOrder) queryString += `&order=${encodeURIComponent(currentOrder)}&order_type=${encodeURIComponent(orderType)}`;
    if (exportCsv) queryString += `&export=${encodeURIComponent(exportCsv)}`;

    //endpoint
    const searchPath = currentQuery ? `/search/${encodeURIComponent(currentQuery)}` : '/search';
    return `/admin/UserManagementController${searchPath}?${queryString}`;
};


//export csv
const exportUsers = () => {
    const exportBtn = document.getElementById("exportBtn");
    if (!exportBtn) { console.log("non trovato"); return; }

    //quando il bottone viene cliccato
    exportBtn.addEventListener('click', () => {
        exportBtn.disabled = true;

        //chiamata ajax: endpoint con export a trye
        fetch(buildUsersUrl(1, true))
            .then(response => {
                if (!response.ok) throw new Error("Errore nel download");
                return response.blob();//server risponde con un blob
            })
            .then(blob => {
                //elementi temporeanei
                const url = window.URL.createObjectURL(blob);//url che punta a blob
                const a = document.createElement('a');//link per accedere a doucmento
                a.style.display = 'none';
                a.href = url;

                a.download = `export_utenti_${formatDate(new Date())}.csv`;//nome del file

                document.body.appendChild(a);
                a.click();//scarica il file

                //rimozione elementi temporanei
                window.URL.revokeObjectURL(url);
                a.remove();
                exportBtn.disabled = false;
            })
            .catch(error => {
                console.error("Errore:", error);
                exportBtn.disabled = false;
            });
    });
};

//ricerca dinamica da un solo input: nome, cognome, email o combinazioni di parole
const searchUser = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadUsers(1);
    });
};



/*
 * Carica utenti con richiesta AJAX, passando pagina, ricerca, filtri e ordinamento.
 * Dopo la risposta aggiorna tabella e paginazione senza reload della pagina.
 */
const loadUsers = (page = 1) => {
    //uso fetch asincrono per aggiornare solo i dati necessari e preservare lo stato della pagina
    fetch(buildUsersUrl(page))
        .then(res => res.json())
        .then(data => {
            renderUsers(data.users);
            renderPagination(data.pagination, loadUsers);//callaback
        });

    seeFilters();//aggiorna dropdown con filtro corrente
}

const seeFilters = () => {
    //evidenzia nei dropdown i filtri attivi, cosi lo stato AJAX resta visibile
    document.querySelectorAll('button[data-role_id]').forEach(btn => {
        if (btn.dataset.role_id === currentRole) {
            btn.classList.add('bg-primary', 'text-white');
        } else {
            btn.classList.remove('bg-primary', 'text-white');
        }


    });


    document.querySelectorAll('button[data-level_id]').forEach(btn => {
        if (btn.dataset.level_id === currentLevel) {
            btn.classList.add('bg-primary', 'text-white');
        } else {
            btn.classList.remove('bg-primary', 'text-white');
        }
    });
}

//renderizza gli utenti usando un DocumentFragment per aggiornare il DOM una volta sola
const renderUsers = (users) => {
    const tbody = document.getElementById('usersTableBody');
    //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
    tbody.innerHTML = '';

    //il fragment riduce repaint/reflow quando la tabella viene ricreata spesso
    const fragment = document.createDocumentFragment();
    users.forEach((user, index) => fragment.appendChild(createUserRow(user, index)));
    //inserimento come figlo nel tbody
    tbody.appendChild(fragment);
};



//riga costruita da un oggetto utente e dall'indice usato per il colore avatar sequenziale
const createUserRow = (user, index) => {
    //il colore dell'avatar cambia in sequenza : index=0 % 5 = 0 (primary), index=1 % 5 = 1 (success), index=2 % 5 = 2 (warning), index=3 % 5 = 3 (danger), index=4 % 5 = 4 (info), index=5 % 5 = 0 (primary), ecc.
    const colorClass = avatarColors[index % avatarColors.length];

    //ruolo badge: red: Admin, gray: User
    let role_id = new Number(user.role_id);
    const roleBadge = role_id == 1 ? 'bg-danger' : 'bg-secondary';

    //lievello badge: verde: Principiante, blu: Intermedio, giallo: Avanzato
    const lvlBadgeMap = {
        1: 'bg-success',
        2: 'bg-primary',
    };
    const lvlBadge = lvlBadgeMap[user.level_id] ?? 'bg-warning text-dark';

    //il <template> tiene HTML e JS separati: qui cloniamo solo la struttura da riempire
    const template = document.getElementById('userRowTemplate');
    //crea una copia di template  (anche i suoi figli) e prende la riga(tr), ritorna un fragment non ancora renderizzato
    const tr = template.content.cloneNode(true).querySelector('tr');

    //i data-field evitano querySelector fragili basati sulla posizione delle colonne
    tr.querySelector('[data-field="user_id"]').textContent = `#${user.user_id}`;
    tr.querySelector('[data-field="avatar"]').classList.add(colorClass);
    tr.querySelector('[data-field="avatar"]').textContent = getInitials(user.first_name, user.last_name);
    tr.querySelector('[data-field="full_name"]').textContent = `${user.first_name} ${user.last_name}`;
    tr.querySelector('[data-field="email"]').textContent = user.email;
    tr.querySelector('[data-field="role_id"]').className += ` ${roleBadge}`;
    tr.querySelector('[data-field="role_id"]').textContent = ucFirst(user.role);
    tr.querySelector('[data-field="level_id"]').className += ` ${lvlBadge}`;
    tr.querySelector('[data-field="level_id"]').textContent = user.level || '-';
    tr.querySelector('[data-field="portfolio_count"]').textContent = Number(user.portfolio_count || 0);
    tr.querySelector('[data-field="created_at"]').textContent = formatDate(user.created_at);
    tr.querySelector('[data-field="manage_btn"]').dataset.id = user.user_id;

    return tr;
};

//filtro per livello: aggiorna lo stato e ricarica dalla prima pagina
const filterByLevel = () => {
    //event delegation: intercetto i click da un unico listener anche su elementi creati dopo
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-level_id]');//dropdown
        if (!btn) return;

        currentLevel = btn.dataset.level_id || 0;//setta stato globale
        loadUsers(1);//utenti filtrati
    });
}

//filtro per ruolo: combinabile con ricerca, livello e ordinamento
const filterByRole = () => {
    //event delegation: intercetto i click da un unico listener anche su elementi creati dopo
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-role_id]');//dropdown
        if (!btn) return;

        currentRole = btn.dataset.role_id || 0;//setta stato globale
        loadUsers(1);//carica utenti filtrati
    });
}


//ordinamento colonne: alterna ASC/DESC se si riclicca la stessa intestazione
const orderBy = () => {

    //event delegation: intercetto i click da un unico listener anche su elementi creati dopo
    document.addEventListener('click', (e) => {

        const th = e.target.closest('a[data-order]');//headers
        if (!th) return;

        const clickedOrder = th.dataset.order;

        if (currentOrder === clickedOrder) {//cambia ordine (asc->desc; desc->asc)
            orderType = orderType === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentOrder = clickedOrder;
            orderType = 'DESC'; //default
        }

        //cambia icone di ordinamneto di tutti gli altre colonne
        document.querySelectorAll('a[data-order]').forEach(header => {
            if (header.dataset.order !== currentOrder) {
                //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
                header.innerHTML = header.textContent + "<i class='fas fa-sort-amount-up ms-1'></i>";
            }
        });


        //aggiunge icona a header
        const icon = document.createElement('i');
        icon.className = orderType === 'ASC'
            ? 'fas fa-sort-amount-up ms-1'
            : 'fas fa-sort-amount-down ms-1';


        //scrivo html dinamico qui per rendere il contenuto velocemente in base ai dati ricevuti
        th.innerHTML = `${th.textContent} ${icon.outerHTML}`;

        loadUsers(1);//carica utenti ordinati

    });

};




const getInitials = (first, last) => { //prime due lettere per icona avatar
    return (((first || '?')[0] || '') + ((last || '?')[0] || '')).toUpperCase();
};

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('it-IT');

const ucFirst = (str) =>
    str.charAt(0).toUpperCase() + str.slice(1);
