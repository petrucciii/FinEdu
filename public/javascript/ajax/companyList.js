import renderPagination from '../control.js';

//define global status for the query
let currentQuery = '';

document.addEventListener('DOMContentLoaded', () => {
    loadCompanies();
    searchCompany();
});

const searchCompany = () => {
    const input = document.getElementById('searchInput');

    input.addEventListener('input', (e) => {
        currentQuery = e.target.value.trim();
        loadCompanies(1, currentQuery); //pass the query to the loader
    });
};

const loadCompanies = (page = 1, query = '') => {
    fetch(`/CompanyController/search/${encodeURIComponent(query)}?page=${page}`)
        .then(res => res.json())
        .then(data => {
            renderCompanies(data.companies);

            //pass callback function
            renderPagination(data.pagination, (newPage) => {
                loadCompanies(newPage, currentQuery);
            });
        })
        .catch(err => console.error("Errore nel caricamento companies:", err)); // Always good to have a catch!
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


const createCompanyRow = (company, index) => {
    const template = document.getElementById('companyRowTemplate');
    const tr = template.content.cloneNode(true).querySelector('tr');

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