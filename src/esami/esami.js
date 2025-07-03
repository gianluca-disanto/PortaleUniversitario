function showTab(tabName) {
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    

    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function filterTable() {
    const corsoFilter = document.getElementById('corsoFilter').value;
    const statoFilter = document.getElementById('statoFilter').value;
    const dataFilter = document.getElementById('dataFilter').value;
    
    const activeTab = document.querySelector('.tab-content.active');
    const rows = activeTab.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        // Filtro per corso
        if (corsoFilter && row.dataset.corso !== corsoFilter) {
            show = false;
        }
        
        // Filtro per stato
        if (statoFilter) {
            const badge = row.querySelector('.badge');
            const stato = badge.textContent.toLowerCase();
            if (statoFilter === 'prenotabile' && stato != 'prenotabile') show = false;
            if (statoFilter === 'completato' && stato != 'completato') show = false;
            if (statoFilter === 'non prenotabile' && stato != 'non prenotabile') show = false;
        }
        
        // Filtro per data
        if (dataFilter && row.dataset.data) { //row.dataset.data mi serve per accedere a data-data='' che si trova nel tag <tr ...
            const rowDate = new Date(row.dataset.data);
            const filterDate = new Date(dataFilter);
            if (rowDate.toDateString() !== filterDate.toDateString()) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
}