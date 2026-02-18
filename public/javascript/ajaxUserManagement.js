
document.addEventListener('DOMContentLoaded', () => {
    settingsModal();
    loadUsers();//load all users when page is loaded
    searchUser();
    filterByLevel();
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

//global status
let currentQuery = '';
let currentRole = '';
let currentLevel = '';

//build the url for fetching users
const buildUsersUrl = (page = 1) => {
    //get parameters building
    let queryString = `page=${page}`;

    //optional filters
    if (currentRole) queryString += `&role=${encodeURIComponent(currentRole)}`;
    if (currentLevel) queryString += `&level=${encodeURIComponent(currentLevel)}`;

    //final URL
    return `/admin/UserManagementController/search/${encodeURIComponent(currentQuery)}?${queryString}`;
};


//search user by name, email or role.
const searchUser = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadUsers(1);
    });
};



//load users with ajax, passing page number as parameter, and using global status for search query and filters. then render users and pagination.
const loadUsers = (page = 1) => {

    fetch(buildUsersUrl(page))
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


//render pagination passing object with currentPage, pageCount, perPage and total. 
const renderPagination = ({ currentPage, pageCount, perPage, total }) => {
    const container = document.getElementById('paginationContainer');
    container.innerHTML = '';

    //left: info about how many users are showing and total users
    const info = document.createElement('span');
    info.className = 'text-muted small';
    const showing = Math.min(perPage, total - (currentPage - 1) * perPage);
    info.textContent = `Mostrando ${showing} di ${total} utenti`;

    //bootstrap pagination components
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0 shadow-sm rounded';

    //how many pages before and after the current one.
    const surroundCount = 2;//max 2 pages before and 2 pages after

    //if number is greater than pageCount (last page), end at pageCount, otherwise end at currentPage + surroundCount
    // page 3 + 2 -> range ends at page 5, but if pageCount is 4, range ends at page 4.
    const rangeEnd = Math.min(pageCount, currentPage + surroundCount);
    //if number is less than 1 (starting page), start from 1, otherwise start from currentPage - surroundCount 
    //  page 3 - 2 -> range starts from page 1.
    const rangeStart = Math.max(1, currentPage - surroundCount);

    //create an item for pagination passing an object that contains the label, the page to load, the active and disabled state, and if it's an icon (for styling purposes)
    const createPageItem = ({ label, page, active = false, disabled = false, isIcon = false }) => {
        const li = document.createElement('li');
        //bootstrap clasess
        li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');


        if (disabled) {
            //if disabled, create a span and not a link
            li.innerHTML = `<span class="page-link border-0 px-3 text-muted">${label}</span>`;
        } else {
            const a = document.createElement('a');
            //if not didasbled, create a link and if it is not an icon doesn't add classes
            a.className = 'page-link border-0 px-3' + (isIcon ? '' : ' fw-bold mx-1 rounded');
            a.href = '#';//page loaded with ajax so href not neccesary
            a.innerHTML = label;
            //preventDefault to avoid link usaual behavior with # as href, and load users of the page clicked
            a.addEventListener('click', (e) => { e.preventDefault(); loadUsers(page); });
            li.appendChild(a);
        }

        return li;
    };


    if (currentPage > 1) {
        //previous and first page button active if current page is after first page
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-left small"></i>', page: 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', page: currentPage - 1, isIcon: true }));
    } else {
        //previous page button disabled if current page is the first page. first page absent because it is not necessary to go to the first page if we are already on it.
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', disabled: true }));
    }

    // other pages, from rangeStart to rangeEnd, active state if page is the current page
    for (let i = rangeStart; i <= rangeEnd; i++) {
        ul.appendChild(createPageItem({ label: i, page: i, active: i === currentPage }));
    }


    if (currentPage < pageCount) {
        //next and last page button active if current page is before last page
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', page: currentPage + 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-right small"></i>', page: pageCount, isIcon: true }));
    } else {
        //next page button disabled if current page is the last page. last page absent because it is not necessary to go to the last page if we are already on it.
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', disabled: true }));
    }

    nav.appendChild(ul);

    //DOM rendering
    container.appendChild(info);
    container.appendChild(nav);
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

//filter users by level, when dropdown item is clicked
const filterByLevel = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-level]');//dropdown buttons
        if (!btn) return;

        currentLevel = btn.dataset.level || '';//set status
        loadUsers(1);//load users with new filter
    });
}

//filter users by level, when dropdown item is clicked
const filterByRole = () => {
    document.addEventListener('click', (e) => {
        let btn = e.target.closest('button[data-role]');//dropdown buttons
        if (!btn) return;

        currentRole = btn.dataset.role || '';//set status
        loadUsers(1);//load users with new filter
    });
}



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