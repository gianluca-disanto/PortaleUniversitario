let selectedUserType = 'docente';


function forgotPassword() {
    alert('Funzione di recupero password non ancora implementata.\nContatta l\'amministratore di sistema.');
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const errorMessage = document.getElementById('errorMessage');

    errorMessage.style.display = 'none';
    errorMessage.textContent = '';

    const emailRegex = /^[^\s@]+@university\.it$/;

    // Validazioni
    if (!email.value || !emailRegex.test(email.value)) {
        e.preventDefault();
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'Inserisci un indirizzo email valido.';
        email.focus();
        return;
    }
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[?!@%]).{8,}$/;
    if (!password.value || password.value.length < 0 || !passwordRegex.test(password.value)) {
        e.preventDefault();
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'La password deve contenere almeno 6 caratteri.';
        password.focus();
        return;
    }
    loginButton.textContent = 'Accesso in corso...';
    loginButton.disabled = true;
  
    const formData = new FormData(this);

    fetch("loginAuth.php",{
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success){
            window.location.href = "../dashboard/dashboard.php";
        } else{
            e.preventDefault();
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';
        }
    })
    .catch(error => {
        console.error("Errore durante la fetch: ", error);
    });
    loginButton.textContent = 'Accedi al Sistema';
    loginButton.disabled = false;
});

function resetButton() {
    const loginButton = document.getElementById('loginButton');
    loginButton.textContent = 'Accedi al Sistema';
    loginButton.disabled = false;
    loginButton.style.background = '';
}

function resetForm() {
    document.getElementById('loginForm').reset();
    resetButton();
    document.getElementById('errorMessage').style.display = 'none';
}





