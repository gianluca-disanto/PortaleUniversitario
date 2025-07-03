function inserimentoManuale(){
    const indicatore = this.parentElement;
    console.log(indicatore);
    const findSelect = this.parentElement.querySelector('select[name="indicatori[]"]');
    if (!findSelect) {
        console.warn("Select non trovata");
        return;
    }


    const newElement = document.createElement('input');
    newElement.type = 'text';
    newElement.className = 'indicator-desc';
    newElement.placeholder = 'Descrizione indicatore';
    newElement.name = 'indicatori[]';
    
    findSelect.replaceWith(newElement);
    
   // '<input type="text" class="indicator-desc" placeholder="Descrizione indicatore">';
    
}

function toggleStudent(id) {
    const section = document.getElementById('section-' + id);
    const icon = document.getElementById('icon-' + id);
    
    if (section.classList.contains('active')) {
        section.classList.remove('active');
        icon.textContent = '▼';
        icon.classList.remove('rotated');
    } else {
        section.classList.add('active');
        icon.textContent = '▲';
        icon.classList.add('rotated');
    }
}

function toggleVotoField(id) {

    const form = document.getElementById(`form-${id}`);
    const statoSelect = form.querySelector('select[name="stato"]');
    const votoSelect = document.getElementById(`voto-${id}`);
    const lodeCheckbox = form.querySelector('input[name="lode"]');
    const completato = form.dataset.completato;
    const studente = document.getElementById('section-'+id);
    const indicatori = studente.querySelector('.indicators-section').querySelector('button');
    const argomenti = studente.querySelector('.arguments-section').querySelector('button');
    console.log("FORM: "+form+"\nstatoSelect: "+statoSelect+"\nvotoSelect: "+votoSelect+
    "\nlodeCheckBox: "+lodeCheckbox+"\nCompletato: "+completato+"\nStudente: "+studente+
    "\nIndicatori: "+indicatori+"\nArgomenti: "+argomenti);
    
    if(completato ==='true'){
        statoSelect.disabled = true;
        votoSelect.disabled = true;
        lodeCheckbox.disabled = true;
        indicatori.disabled = true;
        argomenti.disabled = true;
        return;
    }
    console.log(statoSelect.value);
    if (statoSelect.value === 'Assente' || statoSelect.value == '') {
        votoSelect.disabled = true;
        lodeCheckbox.disabled = true;
        votoSelect.value = '';
        lodeCheckbox.checked = false;
        indicatori.disabled = true;
        argomenti.disabled = true;
    } else if(statoSelect.value ==='Presente') {
        votoSelect.disabled = false;
        lodeCheckbox.disabled = false;
        indicatori.disabled = false;
        argomenti.disabled = false;
        if(votoSelect.value !== "30"){
            lodeCheckbox.disabled = true;
        } else {
            lodeCheckbox.disabled = false;
        }
    }
}

function salvaVoti(idEsame){
    const informazioni = document.querySelectorAll(".grade-section");
    const forms = document.querySelectorAll(".grade-form");
    const datiTotali = [];

 //   forms.forEach(form => {

    for(let count = 0; count < forms.length; count++){
        const form = forms[count];
        const id = form.id.value;
    

        const formData = new FormData(form);
        const dati = {};



        for (const [chiave, valore] of formData.entries()) {
            dati[chiave] = valore;
            
        }
        if(!('voto' in dati)){
            dati['voto'] = '';
        }
        if (!("lode" in dati)) {
            dati["lode"] = 0;
        }
        if(dati['stato'] === '' || (dati['stato'] === 'Presente' && dati['voto'] === '')){
            alert("Form non compilato interamente");
            console.error("Form non compilato interamente");
            throw 500;
        }
        
        
        const listaIndicatori = document.getElementById("indicators-"+id);
        const indicatori = listaIndicatori.querySelectorAll(".indicator-item");

        for(let i = 0; i < indicatori.length; i++){
            const indicatore = indicatori[i];
            const inputValore = indicatore.querySelector("input.indicator-value");
            const valoreIndicatore = inputValore.value;

            const selectIndicatore = indicatore.querySelector('select[name="indicatori[]"]');
            const inputIndicatore = indicatore.querySelector('input.indicator-desc');

            let idIndicatore = null;
            let descrizioneIndicatore = '';

            if(valoreIndicatore<=0 || valoreIndicatore >10 || valoreIndicatore == null || valoreIndicatore == ''){
                inputValore.setCustomValidity("Il valore deve essere compreso da 1 e 10, estremi inclusi");
                inputValore.reportValidity();
                return;
            }
        
            
            if(selectIndicatore){
                if(selectIndicatore.value == ''){
                    selectIndicatore.setCustomValidity("Valore non valido. Impostare un valore non nullo");
                    selectIndicatore.reportValidity();
                    return;
                }
                idIndicatore = selectIndicatore.value;
            }else if(inputIndicatore){
                if(inputIndicatore.value.trim() == ''){
                    inputIndicatore.setCustomValidity("Valore non valido. Impostare un valore non nullo");
                    inputIndicatore.reportValidity();
                    return;
                }
                descrizioneIndicatore = inputIndicatore.value;
            } else {
                alert("Indicatore mancante per l'indicatore "+i+" dello studente "+id);
                throw new Error("Indicatore mancante nel form "+id);
            }

            if((!idIndicatore && descrizioneIndicatore.trim() === '') || valoreIndicatore === ''){
                alert("Informazioni mancanti per l'indicatore "+i+" dello studente "+id+"\nEliminare l'indicatore se vuoto");
                throw new Error("Informazioni mancanti per l'indicatore "+i+" del form "+id);
            }
            dati['idInd'+i] = idIndicatore;
            dati['indDesc'+i] = descrizioneIndicatore; 
            dati['indValore'+i] = valoreIndicatore;
        }
        dati['numeroIndicatori'] = indicatori.length;


        const listaArgomenti = document.getElementById("arguments-"+id);
        const argomenti = listaArgomenti.querySelectorAll(".argument-item");

        for(let i = 0; i < argomenti.length; i++){
            const argomento = argomenti[i];
            const tipologiaArgomento = argomento.querySelector('select[name="tipologia"]').value;
            const descrizioneArgomento = argomento.querySelector("input.argument-desc").value;
            if(tipologiaArgomento == '' || descrizioneArgomento == ''){
                //indicatore.classList.add('input-error');
                alert("Informazioni mancanti per l'argomento "+i+" dello studente "+id+"\nEliminare l'argomento se vuoto");
                throw new Error("Informazioni mancanti per l'argomento "+i+" del form "+id);
            }
            dati['tipoArgomento'+i] = tipologiaArgomento; 
            dati['descArgomento'+i] = descrizioneArgomento;
        }


        dati['numeroArgomenti'] = argomenti.length;


        datiTotali.push(dati);
        console.log(datiTotali);
    }

fetch('salva_voto.php?esame='+idEsame, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(datiTotali)
})
.then(async response => {
    const contentType = response.headers.get("content-type");

    // Controlla se la risposta è JSON
    if (contentType && contentType.indexOf("application/json") !== -1) {
        const data = await response.json();

        if (response.ok && data.success == true) {
            console.log("Salvataggio riuscito:", data);
            alert("Dati salvati correttamente!");
        } else {
            console.error("Errore dal server: ", data);
            alert("Errore: " + (data.message || "Errore sconosciuto"));
        }

    } else {
        const text = await response.text();
        console.error("Risposta non JSON:", text);
        alert("Errore imprevisto. Log in console");
    }
})
.then(location.reload())
.catch(error => {
    console.error("Errore di rete o fetch:", error);
    alert("Errore di rete: impossibile contattare il server.");
});
}


function aggiungiArgomento(idPrenotazione) {
    const container = document.getElementById('arguments-' + idPrenotazione);
    const newIndicator = document.createElement('div');
    newIndicator.className = 'argument-item';
    newIndicator.innerHTML = `
        <label for="tipologia">Tipologia di domanda:</label>
        <select name="tipologia">
            <option value=""></option>
            <option value="scritto">Scritto</option>
            <option value="orale">Orale</option>

        </select>
        <input type="text" class="argument-desc" placeholder="Argomento">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">🗑️</button>
    `;
    container.appendChild(newIndicator);
}


function aggiungiIndicatore(idPrenotazione) {
    const container = document.getElementById('indicators-' + idPrenotazione);
    const template = document.getElementById('template-indicatore');
    const clone = template.content.cloneNode(true);
    container.appendChild(clone);
}

document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll("form[id^='form-']");
    forms.forEach(form => {
        const idFull = form.getAttribute("id");
        const id = idFull.split("-")[1];

        const votoSelect = document.getElementById(`voto-${id}`);
        const lodeCheckbox = form.querySelector('input[name="lode"]');
        const studente = document.getElementById(`section-${id}`);
        const indicatori = studente?.querySelector('.indicators-section button');
        const argomenti = studente?.querySelector('.arguments-section button');

        if(votoSelect){
            votoSelect.disabled = true;
            //votoSelect.value = '';
        }
        if(lodeCheckbox){
            lodeCheckbox.disabled = true;
        }
        
        
        if(indicatori) indicatori.disabled = true;
        if(argomenti) argomenti.disabled = true;
    });
});

function valoreIndicatore(element){
    if(element.value <= 0){
        element.value = 1;
    }else if(element.value >10){
        element.value = 10;
    }
}