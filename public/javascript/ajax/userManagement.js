import renderPagination from '../control.js';

document.addEventListener('DOMContentLoaded', () => {
    settingsModal();
    loadUsers();//carica gli utenti quando pagina è caricata (piccolo delay)
    searchUser();
    filterByLevel();
    filterByRole();
    orderBy();
    exportUsers();
});

//finestra modale per gestione utente (aperta con ajax)
const settingsModal = () => {
    //gestita sul documento e non su ogni bottone dato che ce n'è uno per ogni riga
    document.addEventListener('click', (e) => {

        let btn = e.target.closest('.open-user-btn');
        if (!btn) return;

        //prende id da data-attribute
        let userId = btn.dataset.id;

        //fetch ajax all'endpoint con userId
        fetch('/admin/UserManagementController/settings/' + userId)//endpoint : UserManagement::settings($userId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore server');
                }

                return response.json();
            })
            .then(data => {
                //popola modal
                document.getElementById('modalUserTitle').textContent = " Utente #" + data.user.user_id;
                document.getElementsByClassName('modalInputFirstName')[0].value = data.user.first_name;
                document.getElementsByClassName('modalInputLastName')[0].value = data.user.last_name;
                document.getElementsByClassName('modalInputEmail')[0].value = data.user.email;
                document.getElementById('modalCreatedAt').textContent = new Date(data.user.created_at).toLocaleDateString();
                document.getElementById('modalLevel').textContent = data.user.level;

                //inseisce attributi per i form (bottoni) con le varie azioni
                document.querySelectorAll('form[name="modalEditForm"]').forEach(form => {
                    form.action = '/admin/UserManagementController/editColumn/' + userId;
                });
                document.getElementById('modalDeleteForm').action = '/admin/UserManagementController/delete/' + userId;

                //radio buttons per i ruoli
                let rolesContainer = document.getElementById('modalRolesContainer');
                rolesContainer.innerHTML = "";//pulisce il container per evitare duplicazioni

                //mette dentro il container ogni ruolo e seleziona quello corrente
                data.roles.forEach(role => {
                    let div = document.createElement('div');
                    div.className = "form-check form-check-inline";
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

//costrusice url per endpoint in base a stato
const buildUsersUrl = (page = 1, exportCsv = false) => {
    //parametri get
    let queryString = `page=${page}`;

    //filtri
    if (currentRole) queryString += `&role_id=${encodeURIComponent(currentRole)}`;
    if (currentLevel) queryString += `&level_id=${encodeURIComponent(currentLevel)}`;
    if (currentOrder) queryString += `&order=${encodeURIComponent(currentOrder)}&order_type=${encodeURIComponent(orderType)}`;
    if (exportCsv) queryString += `&export=${encodeURIComponent(exportCsv)}`;

    //endpoint
    return `/admin/UserManagementController/search/${encodeURIComponent(currentQuery)}?${queryString}`;
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

//ricerca da nome o cognome
const searchUser = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadUsers(1);
    });
};



//carica utenti con richiesta ajax, passando il numero della pagina corrente e l'endpoint creato con stato globale
//gli utenti vengono poi renderizzati e inseriti nella tabella con impaginazione
const loadUsers = (page = 1) => {
    fetch(buildUsersUrl(page))
        .then(res => res.json())
        .then(data => {
            renderUsers(data.users);
            renderPagination(data.pagination, loadUsers);//callaback
        });

    seeFilters();//aggiorna dropdown con filtro corrente
}

const seeFilters = () => {
    //per ogni option conrolla quella attiva
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

//renderizza users nella tabella
const renderUsers = (users) => {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';

    //crea un document fragmented (non renderizzato nel DOM) aggiungendo tutti gli utenti (righe), 
    // così il DOM viene aggiornato solo una volta e non una volta ogni riga
    const fragment = document.createDocumentFragment();
    users.forEach((user, index) => fragment.appendChild(createUserRow(user, index)));
    //inserimento come figlo nel tbody
    tbody.appendChild(fragment);
};



//riga costruita passand utente come oggetto e index per l'icona dell'avatar
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

    //popola <template> con dati utente
    const template = document.getElementById('userRowTemplate');
    //crea una copia di template  (anche i suoi figli) e prende la riga(tr), ritorna un fragment non ancora renderizzato
    const tr = template.content.cloneNode(true).querySelector('tr');

    //riempie colonne utilizzato i data-attribute
    tr.querySelector('[data-field="user_id"]').textContent = `#${user.user_id}`;
    tr.querySelector('[data-field="avatar"]').classList.add(colorClass);
    tr.querySelector('[data-field="avatar"]').textContent = getInitials(user.first_name, user.last_name);
    tr.querySelector('[data-field="full_name"]').textContent = `${user.first_name} ${user.last_name}`;
    tr.querySelector('[data-field="email"]').textContent = user.email;
    tr.querySelector('[data-field="role_id"]').className += ` ${roleBadge}`;
    tr.querySelector('[data-field="role_id"]').textContent = ucFirst(user.role);
    tr.querySelector('[data-field="level_id"]').className += ` ${lvlBadge}`;
    tr.querySelector('[data-field="level_id"]').textContent = user.level;
    tr.querySelector('[data-field="created_at"]').textContent = formatDate(user.created_at);
    tr.querySelector('[data-field="manage_btn"]').dataset.id = user.user_id;

    return tr;
};

//filtro per livello
const filterByLevel = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-level_id]');//dropdown
        if (!btn) return;

        currentLevel = btn.dataset.level_id || 0;//setta stato globale
        loadUsers(1);//utenti filtrati
    });
}

//filtro per ruolo
const filterByRole = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-role_id]');//dropdown
        if (!btn) return;

        currentRole = btn.dataset.role_id || 0;//setta stato globale
        loadUsers(1);//carica utenti filtrati
    });
}


//ordinamento
const orderBy = () => {

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
                header.innerHTML = header.textContent + "<i class='fas fa-sort-amount-up ms-1'></i>";
            }
        });


        //aggiunge icona a header
        const icon = document.createElement('i');
        icon.className = orderType === 'ASC'
            ? 'fas fa-sort-amount-up ms-1'
            : 'fas fa-sort-amount-down ms-1';


        th.innerHTML = `${th.textContent} ${icon.outerHTML}`;

        loadUsers(1);//carica utenti ordinati

    });

};




const getInitials = (first, last) => { //prime due lettere per icona avatar
    return (first[0] + last[0]).toUpperCase();
};

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('it-IT');

const ucFirst = (str) =>
    str.charAt(0).toUpperCase() + str.slice(1);

//previene xss
const escapeHtml = (str) => {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
};
