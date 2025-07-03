function listenerButton(){
    let element = document.querySelector(".tendinaButton");
    element.addEventListener('click', tendina);
}

function tendina(){
    let element = document.querySelector('.menuTendina');
    element.classList.toggle('aperto');
}

document.addEventListener('DOMContentLoaded', listenerButton);


document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
        
            navLinks.forEach(l => l.classList.remove('active'));

            this.classList.add('active'); 
            
        });
    });
});

function toggleMenu() {
    const menu = document.querySelector('.menuTendina');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function rimuoviNotifica(id){
    const notifica = document.querySelector('.notification-item[id="'+id+'"');
    console.log(notifica);
    //notifica.remove();
    const msg = {'rimozione': true, 'idNotifica': id};

    fetch("rimuovi_notifica.php", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(msg)
    })
    .then(async response => {
        const data = await response.json();
        if(data.success){
            notifica.remove();
        }
        console.log(data.message);
    })
    .catch(error =>{
        console.log(error.message);
    })
}