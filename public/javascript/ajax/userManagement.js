import renderPagination from '../control.js';

document.addEventListener('DOMContentLoaded', () => {
    settingsModal();
    loadUsers();//load all users when page is loaded
    searchUser();
    filterByLevel();
    filterByRole();
    orderBy();
    exportUsers();
});

const settingsModal = () => {
    document.addEventListener('click', (e) => {

        //USER SETTINGS MODAL AJAX
        let btn = e.target.closest('.open-user-btn');//iif the clicked element or any of its parents has the class .open-user-btn, it will be assigned to btn, otherwise btn will be null
        if (!btn) return;

        //get user id from data attribute of the button
        let userId = btn.dataset.id;

        //ajax request to get user data by id, then populate and open the modal
        fetch('/admin/UserManagementController/settings/' + userId)//endpoint : UserManagement::settings($userId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore server');
                }

                return response.json();
            })
            .then(data => {
                //populate modal with user data
                document.getElementById('modalUserTitle').textContent = " Utente #" + data.user.user_id;
                document.getElementsByClassName('modalInputFirstName')[0].value = data.user.first_name;
                document.getElementsByClassName('modalInputLastName')[0].value = data.user.last_name;
                document.getElementsByClassName('modalInputEmail')[0].value = data.user.email;
                document.getElementById('modalCreatedAt').textContent = new Date(data.user.created_at).toLocaleDateString();
                document.getElementById('modalLevel').textContent = data.user.level;

                //form action for edit and delete
                document.querySelectorAll('form[name="modalEditForm"]').forEach(form => {
                    form.action = '/admin/UserManagementController/editColumn/' + userId;
                });
                document.getElementById('modalDeleteForm').action = '/admin/UserManagementController/delete/' + userId;

                //roles radiobuttons
                let rolesContainer = document.getElementById('modalRolesContainer');
                rolesContainer.innerHTML = "";//clear previous roles

                //populate radiobuttons with all roles, and check the one that is the current role of the user
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

                //open modal
                let modal = new bootstrap.Modal(
                    document.getElementById('userModal')
                );

                modal.show();


            })
            //other tyoes of errors (network, json, etc.)
            .catch(err => {
                console.error(err);
                alert("Errore caricamento utente");
            });
    });
}
const avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-info'];

//global status
let currentQuery = '';
let currentRole = '';
let currentLevel = '';
let currentOrder = '';
let orderType = 'ASC';

//build the url for fetching users
const buildUsersUrl = (page = 1, exportCsv = false) => {
    //get parameters building
    let queryString = `page=${page}`;

    //optional filters
    if (currentRole) queryString += `&role_id=${encodeURIComponent(currentRole)}`;
    if (currentLevel) queryString += `&level_id=${encodeURIComponent(currentLevel)}`;
    if (currentOrder) queryString += `&order=${encodeURIComponent(currentOrder)}&order_type=${encodeURIComponent(orderType)}`;
    if (exportCsv) queryString += `&export=${encodeURIComponent(exportCsv)}`;

    //final URL
    return `/admin/UserManagementController/search/${encodeURIComponent(currentQuery)}?${queryString}`;
};


//export csv
const exportUsers = () => {
    const exportBtn = document.getElementById("exportBtn");
    if (!exportBtn) { console.log("non trovato"); return; }

    //when btn clicked
    exportBtn.addEventListener('click', () => {
        exportBtn.disabled = true;

        //ajax call with export mode true
        fetch(buildUsersUrl(1, true))
            .then(response => {
                if (!response.ok) throw new Error("Errore nel download");
                return response.blob();//server response is blob format (csv)
            })
            .then(blob => {
                //temporary elements
                const url = window.URL.createObjectURL(blob);//url that points to the blob
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;

                a.download = `export_utenti_${formatDate(new Date())}.csv`;//filename

                document.body.appendChild(a);
                a.click();

                //remove temporary
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

//search user by name, email or role.
const searchUser = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadUsers(1);
    });
};



//load users with ajax, passing page number as parameter, and using global status for search query and filters.
//  then render users and pagination.
const loadUsers = (page = 1) => {
    fetch(buildUsersUrl(page))
        .then(res => res.json())
        .then(data => {
            renderUsers(data.users);
            renderPagination(data.pagination, loadUsers);//callback function
        });

    seeFilters();//update dropdown buttons styles based on current filters, using global status
}

const seeFilters = () => {
    //update dropdown buttons styles based on current filters, using global status
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

//render users in the table
const renderUsers = (users) => {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';

    //create a document fragmented (non rendered in the DOM) to append all the users (rows). so that the DOM is updated only once
    const fragment = document.createDocumentFragment();
    users.forEach((user, index) => fragment.appendChild(createUserRow(user, index)));
    //upload the fragment into the tbody
    tbody.appendChild(fragment);
};



//row built passing user as an object and index for avatar color
const createUserRow = (user, index) => {
    //avatar color based on a loop : index=0 % 5 = 0 (primary), index=1 % 5 = 1 (success), index=2 % 5 = 2 (warning), index=3 % 5 = 3 (danger), index=4 % 5 = 4 (info), index=5 % 5 = 0 (primary), etc.
    const colorClass = avatarColors[index % avatarColors.length];

    //role badge: red: Admin, gray: User
    let role_id = new Number(user.role_id);
    const roleBadge = role_id == 1 ? 'bg-danger' : 'bg-secondary';

    //level badge: green: Principiante, blue: Intermedio, yellow: Avanzato
    const lvlBadgeMap = {
        1: 'bg-success',
        2: 'bg-primary',
    };
    const lvlBadge = lvlBadgeMap[user.level_id] ?? 'bg-warning text-dark';

    //populate <template> with user dat
    const template = document.getElementById('userRowTemplate');
    //create a copy of the template content (its children, too) and select the row(tr), return a fragmented docment not yet rendered in the dom
    const tr = template.content.cloneNode(true).querySelector('tr');

    //fill the the data table row wit user data, and add the color classes for avatar, role and level
    //get the row(tr) and each field(td) by data-field attribute (datatables element)
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

//filter users by level, when dropdown item is clicked
const filterByLevel = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-level_id]');//dropdown buttons
        if (!btn) return;

        currentLevel = btn.dataset.level_id || 0;//set status
        loadUsers(1);//load users with new filter
    });
}

//filter users by level, when dropdown item is clicked
const filterByRole = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-role_id]');//dropdown buttons
        if (!btn) return;

        currentRole = btn.dataset.role_id || 0;//set status
        loadUsers(1);//load users with new filter
    });
}


const orderBy = () => {

    document.addEventListener('click', (e) => {

        const th = e.target.closest('a[data-order]');//header fields
        if (!th) return;

        const clickedOrder = th.dataset.order;

        if (currentOrder === clickedOrder) {//chenge order type
            orderType = orderType === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentOrder = clickedOrder;
            orderType = 'DESC'; //default order type when clicking a new header
        }

        //chaneing icons of all the other headers (besides the clicked one)
        document.querySelectorAll('a[data-order]').forEach(header => {
            if (header.dataset.order !== currentOrder) {
                header.innerHTML = header.textContent + "<i class='fas fa-sort-amount-up ms-1'></i>";
            }
        });


        //add icon to the clicked header
        const icon = document.createElement('i');
        icon.className = orderType === 'ASC'
            ? 'fas fa-sort-amount-up ms-1'
            : 'fas fa-sort-amount-down ms-1';

        //add the icon to the clicked header
        th.innerHTML = `${th.textContent} ${icon.outerHTML}`;

        loadUsers(1);

    });

};




//utility functions
const getInitials = (first, last) => { //first name and last name initials used for avatar, converted to uppercase
    return (first[0] + last[0]).toUpperCase();
};

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('it-IT');

const ucFirst = (str) =>
    str.charAt(0).toUpperCase() + str.slice(1);

// Prevent HTML/JS injection in user data (name, email, ecc.)
const escapeHtml = (str) => {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
};
