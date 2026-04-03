/**
 * Inizializza un editor Quill su un container, sincronizzandolo con un input hidden.
 * Senza toolbar, mantiene la formattazione incollata da Word/Web.
 *
 * @param {string} containerId    - ID del div container Quill (es. 'quillAddContainer')
 * @param {string} hiddenInputId  - ID dell'input hidden che riceve l'HTML (es. 'newsBody')
 * @param {string|null} formId    - ID del form per validazione submit (opzionale)
 * @returns {Quill} istanza Quill creata
 */
function initQuillEditor(containerId, hiddenInputId, formId = null) {
    const quill = new Quill('#' + containerId, {
        modules: {
            toolbar: false,
            clipboard: {
                // previene l'aggiunta di spazi vuoti eccessivi copiando da Word/Web
                matchVisual: false
            }
        },
        theme: 'snow'
    });

    // sincronizza Quill con l'input nascosto su ogni modifica testuale
    quill.on('text-change', function () {
        const htmlContent = quill.root.innerHTML;

        // svuota l'input se l'editor è vuoto (ignora il <p><br></p> di default di Quill)
        if (quill.getText().trim() === '') {
            document.getElementById(hiddenInputId).value = '';
        } else {
            document.getElementById(hiddenInputId).value = htmlContent;
        }
    });

    // validazione prima dell'invio del form
    if (formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function (e) {
                if (document.getElementById(hiddenInputId).value.trim() === '') {
                    e.preventDefault();
                    alert('Il campo contenuto è obbligatorio.');
                }
            });
        }
    }

    return quill;
}
