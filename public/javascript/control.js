/*DRY */

//render pagination passing object with currentPage, pageCount, perPage and total. in addition, onPageChange is the rendering function
function renderPagination({ currentPage, pageCount, perPage, total }, onPageChange) {
    const container = document.getElementById('paginationContainer');
    container.innerHTML = '';

    //left: info about how many elements are showing
    const info = document.createElement('span');
    info.className = 'text-muted small';
    const showing = Math.min(perPage, total - (currentPage - 1) * perPage);
    info.textContent = `Mostrando ${showing} di ${total}`;

    //bootstrap pagination components
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm mb-0 shadow-sm rounded';

    //how many pages before and after the current one.
    const surroundCount = 2; //max 2 pages before and 2 pages after



    //if number is greater than pageCount (last page), end at pageCount, otherwise end at currentPage + surroundCount
    // page 3 + 2 -> range ends at page 5, but if pageCount is 4, range ends at page 4.
    const rangeEnd = Math.min(pageCount, currentPage + surroundCount);
    //if number is less than 1 (starting page), start from 1, otherwise start from currentPage - surroundCount 
    //  page 3 - 2 -> range starts from page 1.
    const rangeStart = Math.max(1, currentPage - surroundCount);

    //create an item for pagination passing an object that contains the label, the page to load, the active and disabled state, and if it's an icon (for styling purposes)
    const createPageItem = ({ label, page, active = false, disabled = false, isIcon = false }) => {
        const li = document.createElement('li');
        //bootstrap clasess
        li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');


        if (disabled) {
            //if disabled, create a span and not a link
            li.innerHTML = `<span class="page-link border-0 px-3 text-muted">${label}</span>`;
        } else {
            const a = document.createElement('a');
            //if not didasbled, create a link and if it is not an icon doesn't add classes
            a.className = 'page-link border-0 px-3' + (isIcon ? '' : ' fw-bold mx-1 rounded');
            a.href = '#'; //page loaded with ajax so href not neccesary
            a.innerHTML = label;
            //preventDefault to avoid link usaual behavior with # as href, and load elements of the page clicked
            a.addEventListener('click', (e) => { e.preventDefault(); onPageChange(page); });
            li.appendChild(a);
        }

        return li;
    };


    if (currentPage > 1) {
        //previous and first page button active if current page is after first page
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-left small"></i>', page: 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', page: currentPage - 1, isIcon: true }));
    } else {
        //previous page button disabled if current page is the first page. first page absent because it is not necessary to go to the first page if we are already on it.
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-left small"></i>', disabled: true }));
    }

    // other pages, from rangeStart to rangeEnd, active state if page is the current page
    for (let i = rangeStart; i <= rangeEnd; i++) {
        ul.appendChild(createPageItem({ label: i, page: i, active: i === currentPage }));
    }


    if (currentPage < pageCount) {
        //next and last page button active if current page is before last page
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', page: currentPage + 1, isIcon: true }));
        ul.appendChild(createPageItem({ label: '<i class="fas fa-angle-double-right small"></i>', page: pageCount, isIcon: true }));
    } else {
        //next page button disabled if current page is the last page. last page absent because it is not necessary to go to the last page if we are already on it.
        ul.appendChild(createPageItem({ label: '<i class="fas fa-chevron-right small"></i>', disabled: true }));
    }

    nav.appendChild(ul);

    //DOM rendering
    container.appendChild(info);
    container.appendChild(nav);
}

export default renderPagination;