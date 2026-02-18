document.addEventListener('DOMContentLoaded', () => {
    settingsModal();
    loadUsers();//load all users when page is loaded
    searchUser();
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
                    <input class="form-check-input" type="radio" name="new_value" id="inlineRadio${role}"
                        value="${role}">
                    <label class="form-check-label" for="inlineRadio${role}">${role[0].toUpperCase() + role.slice(1)}</label>
                `;
                    if (role == data.user.role) {
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


//search user by name, email or role, with debounce of 300ms to avoid too many requests while typing

const searchUser = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        const query = e.target.value;

        //ajax request to search users by query, then render users and pagination
        fetch('/admin/UserManagementController/search/' + query)
            .then(res => {
                if (!res.ok) throw new Error('Errore server');
                return res.json();
            })
            .then(data => {
                if (!data.users) return;
                renderUsers(data.users);
                renderPagination(data.pagination);
            })
            .catch(err => {
                console.error(err);
                alert('Errore ricerca utenti');
            });
    });
};


//load users with pagination, page number and search query are passed as parameters, default values are page=1 and query='' (all users)

function loadUsers(page = 1, query = '') {
    fetch(`/admin/UserManagementController/search/${query}?page=${page}`)
        .then(res => res.json())
        .then(data => {
            renderUsers(data.users);
            renderPagination(data.pagination);
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


//render pagination buttons, currentPage is the active page, pageCount is the total number of pages

const renderPagination = ({ currentPage, pageCount }) => {
    const container = document.getElementById('paginationContainer');
    container.innerHTML = '';

    for (let i = 1; i <= pageCount; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'btn btn-sm ' + (i === currentPage ? 'btn-primary' : 'btn-light');
        btn.addEventListener('click', () => loadUsers(i));
        container.appendChild(btn);
    }
};


//row built passing user as an object and index for avatar color
const createUserRow = (user, index) => {
    //avatar color based on a loop : index=0 % 5 = 0 (primary), index=1 % 5 = 1 (success), index=2 % 5 = 2 (warning), index=3 % 5 = 3 (danger), index=4 % 5 = 4 (info), index=5 % 5 = 0 (primary), etc.
    const colorClass = avatarColors[index % avatarColors.length];

    //role badge: red: Admin, gray: User
    const roleBadge = user.role.toLowerCase() === 'admin' ? 'bg-danger' : 'bg-secondary';

    //level badge: green: Principiante, blue: Intermedio, yellow: Avanzato
    const lvlBadgeMap = {
        'Principiante': 'bg-success',
        'Intermedio': 'bg-primary',
    };
    const lvlBadge = lvlBadgeMap[user.level] ?? 'bg-warning text-dark';

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
    tr.querySelector('[data-field="role"]').className += ` ${roleBadge}`;
    tr.querySelector('[data-field="role"]').textContent = ucFirst(user.role);
    tr.querySelector('[data-field="level"]').className += ` ${lvlBadge}`;
    tr.querySelector('[data-field="level"]').textContent = user.level;
    tr.querySelector('[data-field="created_at"]').textContent = formatDate(user.created_at);
    tr.querySelector('[data-field="manage_btn"]').dataset.id = user.user_id;

    return tr;
};


//utility functions

//first name and last name initials used for avatar, converted to uppercase
const getInitials = (first, last) =>
    (first[0] + last[0]).toUpperCase();

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