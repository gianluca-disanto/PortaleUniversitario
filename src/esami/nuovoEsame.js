document.addEventListener('DOMContentLoaded', function() {
    const dataInizio = document.getElementById('data_inizio_prenotazione');
    const dataFine = document.getElementById('data_fine_prenotazione');
    const dataEsame = document.getElementById('data_esame');
    
    function validateDates() {
        const inizio = new Date(dataInizio.value);
        const fine = new Date(dataFine.value);
        const esame = new Date(dataEsame.value);
        const oggi = new Date();

        if(dataEsame.value && esame < oggi){
            dataEsame.setCustomValidity('La data dell\'esame non può essere fissata prima di oggi');
        }else{
            dataEsame.setCustomValidity('');
        }


        if (dataInizio.value && dataFine.value && inizio >= fine) {
            dataFine.setCustomValidity('La data di fine prenotazione deve essere successiva alla data di inizio');
        } else {
            dataFine.setCustomValidity('');
        }
        
        if (dataFine.value && dataEsame.value && fine > esame) {
            dataFine.setCustomValidity('La fine delle prenotazioni deve essere precedente la data dell\'esame');
        } else {
            dataFine.setCustomValidity('');
        }
    }
    
    dataInizio.addEventListener('change', validateDates);
    dataFine.addEventListener('change', validateDates);
    dataEsame.addEventListener('change', validateDates);
});