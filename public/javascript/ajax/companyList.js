import './control';

document.addEventListener('DOMContentLoaded', () => {
    loadCompanies();//load all companies when page is loaded
    searchCompany();
});

const searchCompany = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadCompanies(1);
    });
};


const loadCompanies = (page = 1, query = '') => {
    fetch(`CompanyController/search/${urlencode(query)}?page=page`)
        .then(res => res.json())
        .then(data => {
            renderCompanies(data.companies);
            renderPagination(data.pagination);
        });

}

const renderCompanies = (companies) => {
    const tbody = document.getElementById('companiesTableBody');
    tbody.innerHTML = '';

    //create a document fragmented (non rendered in the DOM) to append all the companies (rows). so that the DOM is updated only once
    const fragment = document.createDocumentFragment();
    companies.forEach((company, index) => fragment.appendChild(createCompanyRow(company, index)));
    //upload the fragment into the tbody
    tbody.appendChild(fragment);
};


// Costruisce la riga passando l'oggetto company (che contiene anche i dati del listing)
const createCompanyRow = (company, index) => {

    // Recupera il template e clona il contenuto (creando un fragment non ancora nel DOM)
    const template = document.getElementById('companyRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');

    // --- LOGO ---
    const logoImg = tr.querySelector('[data-field="logo"]');
    if (company.logo_path && company.logo_path.trim() !== '') {
        logoImg.src = company.logo_path; // Supponendo che il percorso dal DB sia corretto o includa la base_url()
    } else {
        // Fallback se la società non ha un logo
        logoImg.src = '/assets/img/default_company.png';
    }

    // --- DATI TESTUALI ---
    tr.querySelector('[data-field="name"]').textContent = company.name;
    tr.querySelector('[data-field="country"]').textContent = company.country;
    tr.querySelector('[data-field="isin"]').textContent = company.isin;
    tr.querySelector('[data-field="ticker"]').textContent = company.ticker;
    tr.querySelector('[data-field="sector"]').textContent = company.sector;
    tr.querySelector('[data-field="mic"]').textContent = company.mic;
    tr.querySelector('[data-field="currency"]').textContent = company.currency;

    // --- BOTTONE AZIONE (Negozia) ---
    // Salviamo Ticker, MIC e ISIN nei data-attribute del bottone. 
    // Quando l'utente cliccherà "Negozia", il tuo JS leggerà questi dati per popolare il Modale.
    const orderBtn = tr.querySelector('[data-field="order_btn"]');
    orderBtn.dataset.ticker = company.ticker;
    orderBtn.dataset.mic = company.mic;
    orderBtn.dataset.isin = company.isin;

    return tr;
};