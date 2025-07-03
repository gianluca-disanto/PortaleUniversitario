function toggleMenu() {
    const menu = document.querySelector('.menuTendina');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Se si clicca in qualsiasi punto della pagina si richiuse il menu, per comodità
document.addEventListener('click', function(event) {
    const tendina = document.querySelector('.tendina');
    const menu = document.querySelector('.menuTendina');
    
    if (!tendina.contains(event.target)) {
        menu.style.display = 'none';
    }
});