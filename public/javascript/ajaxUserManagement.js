document.addEventListener('click', (e) => {

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