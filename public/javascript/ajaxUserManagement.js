document.addEventListener('click', (e) => {

    let btn = e.target.closest('.open-user-btn');//iif the clicked element or any of its parents has the class .open-user-btn, it will be assigned to btn, otherwise btn will be null
    if (!btn) return;

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
            console.log(data);
            //populate modal with user data
            document.getElementById('modalUserTitle').textContent = " Utente #" + data.user_id;
            document.getElementsByClassName('modalInputFirstName')[0].value = data.first_name;
            document.getElementsByClassName('modalInputLastName')[0].value = data.last_name;
            document.getElementsByClassName('modalInputEmail')[0].value = data.email;
            document.getElementById('modalCreatedAt').textContent = new Date(data.created_at).toLocaleDateString();
            document.getElementById('modalLevel').textContent = data.level;

            document.querySelectorAll('form[name="modalEditForm"]').forEach(form => {
                form.action = '/admin/UserManagementController/editColumn/' + userId;
            });
            document.getElementById('modalDeleteForm').action = '/admin/UserManagementController/delete/' + userId;


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