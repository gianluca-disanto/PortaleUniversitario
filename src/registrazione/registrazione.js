document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    const codiceFiscale = document.getElementById('codice_fiscale');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const nome = document.getElementById('nome');
    const cognome = document.getElementById('cognome');
    const indirizzo = document.getElementById('indirizzo');
    const dipartimento = document.getElementById('dipartimento');
    const ruolo = document.getElementById('ruolo');
    const errorMessage = document.getElementById('errorMessage');

    // Reset errori
    errorMessage.style.display = 'none';

    const nomeRegex = /^[a-zA-Zàèéìòù\'\s]+$/;
    if(nome.value.length < 2 || !nomeRegex.test(nome.value)){
        //nome.setCustomValidity("Inserire un nome valido"); //Mi da un bug se poi la condizione non è più vera, come se si dovesse resettare
        errorMessage.textContent = "Nome non valido";
        errorMessage.style.display = 'block';
        return;
    }


    if(cognome.value.length < 2 || !nomeRegex.test(cognome.value)){
        errorMessage.textContent = "Cognome non valido";
        errorMessage.style.display = 'block';
        return;
    }

    const cfRegex = /^[a-z]{6}[0-9]{2}[a-z][0-9]{2}[a-z][0-9]{3}[a-z]$/i;
    if (!cfRegex.test(codiceFiscale.value)) {
        errorMessage.textContent = 'Codice fiscale non valido';
        errorMessage.style.display = 'block';
        return;
    }


    const emailRegex = /^[^\s@]+@university\.it$/;

    if (!emailRegex.test(email.value)) {
        errorMessage.textContent = 'Email non valida. Ricorda che il dominio deve essere @university.it';
        errorMessage.style.display = 'block';
        return;
    }

    
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[?!@%]).{8,}$/;
    if (!passwordRegex.test(password.value)) {
        errorMessage.textContent = 'La password deve essere di almeno 8 caratteri, almeno una maiuscola, almeno una minuscola, almeno una cifra, almeno un simbolo (@?!%)';
        errorMessage.style.display = 'block';
        return;
    }

    if(dipartimento.value === '' ){
        errorMessage.textContent = "E' necessario selezionare un dipartimento di afferenza";
        errorMessage.style.display = 'block';
        return;
    }

    if(ruolo.value === '' ){
        errorMessage.textContent = "E' necessario selezionare un ruolo di inquadramento";
        errorMessage.style.display = 'block';
        return;
    }

    const payload = {
        nome: nome.value,
        cognome: cognome.value,
        codice_fiscale: codiceFiscale.value,
        email: email.value,
        password: password.value,
        indirizzo: indirizzo.value,
        dipartimento: Number(dipartimento.value),
        ruolo: Number(ruolo.value)
    };

    //console.log(JSON.stringify(payload));
    fetch("registrazioneServer.php", {
        method: 'POST',
        headers: {
            'Content-Type' : 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(async response =>{
        const data = await response.json();
        if(data.success){
            errorMessage.classList.remove('error-message');
            errorMessage.classList.add('success-message');
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';
            this.reset();
        
            
        }else{
            errorMessage.classList.remove('success-message');
            errorMessage.classList.add('error-message');
            
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';
        }
    })
    .catch(error => {
        console.error("Errore in fase di registrazione: "+error);
    });
});