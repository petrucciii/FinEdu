//funzione globale per inizializzare un editor quill su un container.
//crea un editor senza toolbar che mantiene la formattazione incollata da word/web.
//sincronizza il contenuto html con un input hidden per l'invio via form.
//parametri:
// - containerId: id del div dove montare quill (es. 'quillAddContainer')
// - hiddenInputId: id dell'input hidden che ricevera l'html (es. 'newsBody')
// - formId: id del form per aggiungere validazione submit (opzionale, puo essere null)
//ritorna l'istanza quill creata (utile per popolarla da js esterno, es. newsManagement.js)
function initQuillEditor(containerId, hiddenInputId, formId = null) {
    //crea l'istanza quill sul container specificato
    const quill = new Quill('#' + containerId, {
        modules: {
            //nessuna toolbar: l'editor mostra solo il testo formattato
            toolbar: false,
            clipboard: {
                //matchVisual false evita che quill aggiunga spazi e margini
                //eccessivi quando si incolla testo da word o pagine web
                matchVisual: false
            }
        },
        theme: 'snow'
    });

    //ogni volta che il testo cambia nell'editor, aggiorna l'input hidden
    //con il contenuto html. questo permette al form standard di inviare
    //il corpo formattato al server senza ajax custom
    quill.on('text-change', function () {
        const htmlContent = quill.root.innerHTML;

        //quill quando e vuoto lascia un <p><br></p> di default.
        //lo svuotiamo per far funzionare la validazione "required" dell'input hidden
        if (quill.getText().trim() === '') {
            document.getElementById(hiddenInputId).value = '';
        } else {
            document.getElementById(hiddenInputId).value = htmlContent;
        }
    });

    //se e stato passato un formId, aggiunge validazione client-side
    //che blocca il submit se il contenuto e vuoto
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
