
//  checkbox 'completato'
document.getElementById('completato').addEventListener('change', function() {
    if (!this.checked) {
        let hiddenInput = document.querySelector('input[name="completato"][type="hidden"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'completato';
            this.parentNode.appendChild(hiddenInput);
        }
        hiddenInput.value = '0';
    } else {
        let hiddenInput = document.querySelector('input[name="completato"][type="hidden"]');
        if (hiddenInput) {
            hiddenInput.remove();
        }
    }
});

// Validazione date
document.getElementById('inizioPrenotazione').addEventListener('change', validateDates);
document.getElementById('finePrenotazione').addEventListener('change', validateDates);

function validateDates() {
    const inizioPrenotazione = document.getElementById('inizioPrenotazione').value;
    const finePrenotazione = document.getElementById('finePrenotazione').value;
    
    if (inizioPrenotazione && finePrenotazione) {
        if (new Date(inizioPrenotazione) >= new Date(finePrenotazione)) {
            alert('La data di inizio prenotazioni deve essere precedente a quella di fine');
            document.getElementById('finePrenotazione').focus();
        }
    }
}

function eliminaEsame(id){
    if(confirm("Eliminare l'esame? Verranno cancellate eventuali prenotazioni")){

        const data = {'idesame' : id , 'eliminare': true};

        fetch("elimina_esame.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const data = await response.json();
            console.log(data);
            console.log('Success: '+data.success+'\nMessage: '+data.message);
            if(data.success){
                alert(data.message);
                window.location.assign("esami.php");
            }else{
                alert(data.message);
            }
        })
        .catch(error => {
            alert("Errore rilevato: "+error);
            console.log(error);
        })
    }
}