import renderPagination from '../control.js';

//variabile globale di stato per ricordare l'ultima ricerca
let currentQuery = '';

document.addEventListener('DOMContentLoaded', () => {
    loadCompanies();
    searchCompany();
});

const searchCompany = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadCompanies(1, currentQuery); //on input  viene caricata la prima pagina con la ricerca corrente
    });
};

const loadCompanies = (page = 1, query = '') => {
    fetch(`/CompanyController/search/${encodeURIComponent(query)}?page=${page}`)
        .then(res => res.json())
        .then(data => {
            renderCompanies(data.companies);

            //funzione callback, al cambio pagina viene ricaricata con stato corrente (pagina e ricerca)
            renderPagination(data.pagination, (newPage) => {
                loadCompanies(newPage, currentQuery);
            });
        })
        .catch(err => console.error("Errore nel caricamento companies:", err)); 
}

const renderCompanies = (companies) => {
    const tbody = document.getElementById('companiesTableBody');
    tbody.innerHTML = '';

    //crea un DocumentFragment (non renderizzato nel DOM) mettendo tutte le righe della società. così da fare un'unica operazione di inserimento nel DOM (più efficiente)
    const fragment = document.createDocumentFragment();
    companies.forEach((company, index) => fragment.appendChild(createCompanyRow(company, index)));
    //inserimento fragment nel DOM
    tbody.appendChild(fragment);
};


//crea una riga della tabella 
const createCompanyRow = (company, index) => {
    //copia elemento template (riga non renderizzata)
    const template = document.getElementById('companyRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');

    //inserisce dati dellq società nella riga clonata
    const logoImg = tr.querySelector('[data-field="logo"]');
    if (company.logo_path && company.logo_path.trim() !== '') {
        logoImg.src = company.logo_path;
    } else {
        logoImg.src = '/images/logos/default_company.png';
    }

    tr.querySelector('[data-field="name"]').textContent = company.name;
    tr.querySelector('[data-field="country"]').textContent = company.country;
    tr.querySelector('[data-field="isin"]').textContent = company.isin;
    tr.querySelector('[data-field="sector"]').textContent = company.description || 'N/D';


    const editBtn = tr.querySelector('[data-field="edit_btn"]'); //bottone per Admin
    const viewBtn = tr.querySelector('[data-field="view_btn"]'); //bottone per Utente

    //se esiste bottone admin
    if (editBtn) {
        editBtn.href = `/admin/CompanyManagementController/edit/${encodeURIComponent(company.isin)}`;
    }

    //se esiste bottone per utente
    if (viewBtn) {
        viewBtn.href = `/CompanyController/viewCompany/${encodeURIComponent(company.isin)}`;
    }

    return tr;
};