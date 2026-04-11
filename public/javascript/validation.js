//seleziona gli elementi (prendendo il primo della lista [0], quello che ci interessa e l'unico che c'è)
const newPassword = document.getElementsByName("new_password")[0];
const repeatPassword = document.getElementsByName("repeat_password")[0];
const passwordSubmitButton = document.getElementsByName("password_change")[0];
const validationPasswordFeedback = document.getElementById('validationPasswordFeedback');

const validatePasswords = () => {
    const val1 = newPassword.value;
    const val2 = repeatPassword.value;

    //mostra errore se non sono uguali
    if (val1 !== val2 && val2 !== "") {
        validationPasswordFeedback.style.display = "block";
        passwordSubmitButton.disabled = true;
        repeatPassword.classList.add("is-invalid");

    } else {//reset errori
        validationPasswordFeedback.style.display = "none";
        passwordSubmitButton.disabled = false;
        repeatPassword.classList.remove("is-invalid");
    }
};


newPassword.addEventListener('input', validatePasswords);
repeatPassword.addEventListener('input', validatePasswords);