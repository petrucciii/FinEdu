document.addEventListener('DOMContentLoaded', () => {
    //inizializza i listener della gestione admin educazione
    bindModuleEdit();
    bindLessonAdd();
    bindLessonEdit();
    bindLessonTypeToggle();
    bindConfirmSubmit();
});

const bindModuleEdit = () => {
    //popola il modal modifica modulo dai data attribute del bottone
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-module-edit');
        if (!btn) return;

        document.getElementById('edit_module_id').value = btn.dataset.id || '';
        document.getElementById('edit_module_name').value = btn.dataset.name || '';
        document.getElementById('edit_module_description').value = btn.dataset.description || '';
    });
};

const bindLessonAdd = () => {
    //prepara il modal nuova lezione con id e nome modulo corrente
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-lesson-add');
        if (!btn) return;

        const form = document.getElementById('addLessonForm');
        if (form) form.reset();

        document.getElementById('add_lesson_module_id').value = btn.dataset.moduleId || '';
        document.getElementById('add_lesson_module_name').textContent = btn.dataset.moduleName || '';
        toggleAddLessonFields();
    });
};

const bindLessonEdit = () => {
    //popola il modal modifica lezione e mostra campi diversi per quiz o spiegazione
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-lesson-edit');
        if (!btn) return;

        const type = btn.dataset.type || 'lesson';
        document.getElementById('edit_lesson_id').value = btn.dataset.id || '';
        document.getElementById('edit_lesson_title').value = btn.dataset.title || '';
        document.getElementById('edit_lesson_description').value = btn.dataset.description || '';
        document.getElementById('edit_lesson_hint').value = btn.dataset.hint || '';
        document.getElementById('edit_lesson_type_label').value = type === 'quiz' ? 'Quiz' : (type === 'explanation' ? 'Spiegazione' : 'Non configurata');
        document.getElementById('edit_lesson_body').value = btn.dataset.body || '';

        const expFields = document.getElementById('editFieldsExplanation');
        const quizFields = document.getElementById('editFieldsQuiz');
        if (type === 'explanation') {
            expFields.classList.remove('d-none');
            quizFields.classList.add('d-none');
        } else {
            expFields.classList.add('d-none');
            quizFields.classList.remove('d-none');
        }
    });
};

const bindLessonTypeToggle = () => {
    //aggiorna i campi visibili quando cambia il tipo lezione
    const select = document.getElementById('addLessonTypeSelect');
    if (!select) return;
    select.addEventListener('change', toggleAddLessonFields);
    toggleAddLessonFields();
};

const toggleAddLessonFields = () => {
    //mostra solo i campi coerenti con la specializzazione scelta
    const select = document.getElementById('addLessonTypeSelect');
    const expFields = document.getElementById('addFieldsExplanation');
    const quizFields = document.getElementById('addFieldsQuiz');
    if (!select || !expFields || !quizFields) return;

    if (select.value === 'quiz') {
        expFields.classList.add('d-none');
        quizFields.classList.remove('d-none');
    } else {
        expFields.classList.remove('d-none');
        quizFields.classList.add('d-none');
    }
};

const bindConfirmSubmit = () => {
    //gestisce le conferme di eliminazione senza javascript inline nella view
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('.confirm-submit');
        if (!form) return;

        const message = form.dataset.confirm || 'Confermare operazione?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
};
