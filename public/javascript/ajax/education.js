document.addEventListener('DOMContentLoaded', () => {
    //inizializza i form ajax della parte utente educazione
    bindEducationForms();
});

const bindEducationForms = () => {
    //intercetta sia completamento spiegazione sia invio quiz
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('.education-explanation-form, .education-quiz-form');
        if (!form) return;

        e.preventDefault();
        submitEducationForm(form);
    });
};

const submitEducationForm = (form) => {
    //invia il form al controller mantenendo formdata e fallback server
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        //blocca il bottone per evitare doppi tentativi involontari
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Invio...';
    }

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
        .then(async (response) => {
            //prova sempre a leggere json, anche quando il server ritorna errore
            const data = await response.json().catch(() => ({
                success: false,
                message: 'Risposta server non valida.',
                alert_type: 'danger',
            }));

            if (!response.ok) data.success = false;
            return data;
        })
        .then((data) => {
            //trova la card lezione da aggiornare senza ricaricare pagina
            const lessonItem = form.closest('.education-lesson-item');
            if (!lessonItem) return;

            showLessonFeedback(lessonItem, data.message || 'Operazione completata.', data.alert_type || (data.success ? 'success' : 'danger'));

            if (data.state) {
                //applica progressi, badge e sblocco lezioni arrivati dal controller
                applyEducationState(data.state, Number(data.lesson_id || 0));
            }

            if (data.success && data.completed) {
                //se la lezione e completata sostituisce il form con stato finale
                renderCompletedResult(lessonItem);
                return;
            }

            if (submitBtn) {
                //se il quiz e sbagliato o c'e un errore, permette di riprovare
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }

            if (!data.success && data.redirect && data.redirect !== window.location.pathname) {
                console.warn('Redirect suggerito:', data.redirect);
            }
        })
        .catch((err) => {
            //gestisce errori di rete senza perdere la pagina corrente
            console.error(err);
            const lessonItem = form.closest('.education-lesson-item');
            if (lessonItem) {
                showLessonFeedback(lessonItem, 'Errore di connessione. Riprova.', 'danger');
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
};

const applyEducationState = (state, completedLessonId = 0) => {
    /*applica lo stato calcolato dal backend.
    il backend resta la fonte vera per completamenti e blocchi,
    il javascript aggiorna solo la ui in modo immediato.*/
    updateModuleProgress(state.module_progress || {});

    //memorizza lo stato precedente per capire quale lezione si e appena sbloccata
    const previousStatuses = new Map();
    document.querySelectorAll('.education-lesson-item').forEach((item) => {
        previousStatuses.set(Number(item.dataset.lessonId), item.dataset.status || '');
    });

    let lessonToOpen = null;

    (state.lessons || []).forEach((lesson) => {
        //aggiorna ogni lezione presente nell'accordion
        const item = document.querySelector(`.education-lesson-item[data-lesson-id="${lesson.id_lesson}"]`);
        if (!item) return;

        const previousStatus = previousStatuses.get(Number(lesson.id_lesson));
        item.dataset.status = lesson.status;
        updateLessonBadge(item, lesson.status);
        updateLessonPanels(item, lesson.status);

        if (lesson.completed) {
            renderCompletedResult(item);
        }

        if (previousStatus === 'locked' && lesson.status === 'available' && Number(lesson.id_lesson) !== completedLessonId && !lessonToOpen) {
            //apre automaticamente la prima nuova lezione sbloccata
            lessonToOpen = item;
        }
    });

    if (lessonToOpen) {
        openLessonCollapse(lessonToOpen);
    }
};

const updateModuleProgress = (progress) => {
    //aggiorna percentuale e contatore lezioni completate
    const percent = Number(progress.progress_percent || 0);
    const completed = Number(progress.completed_count || 0);
    const total = Number(progress.lesson_count || 0);

    const bar = document.getElementById('moduleProgressBar');
    const percentText = document.getElementById('moduleProgressPercent');
    const countText = document.getElementById('moduleProgressCount');

    if (bar) {
        bar.style.width = `${percent}%`;
        bar.setAttribute('aria-valuenow', String(percent));
    }
    if (percentText) percentText.textContent = `${percent}%`;
    if (countText) countText.textContent = `${completed} / ${total} lezioni completate`;
};

const updateLessonBadge = (item, status) => {
    //aggiorna il badge visivo della lezione in base allo stato
    const badge = item.querySelector('.lesson-status-badge');
    if (!badge) return;

    const badgeMap = {
        completed: {
            className: 'badge bg-success me-3 rounded-pill lesson-status-badge',
            html: '<i class="fas fa-check me-1"></i>Completata',
        },
        available: {
            className: 'badge bg-secondary me-3 rounded-pill lesson-status-badge',
            html: '<i class="fas fa-clock me-1"></i>Da fare',
        },
        locked: {
            className: 'badge bg-light text-dark border me-3 rounded-pill lesson-status-badge',
            html: '<i class="fas fa-lock text-muted me-1"></i>Bloccata',
        },
    };

    const config = badgeMap[status] || badgeMap.available;
    badge.className = config.className;
    badge.innerHTML = config.html;
};

const updateLessonPanels = (item, status) => {
    //alterna pannello bloccato e contenuto reale della lezione
    const lockPanel = item.querySelector('.lesson-lock-panel');
    const contentPanel = item.querySelector('.lesson-content-panel');
    if (!lockPanel || !contentPanel) return;

    if (status === 'locked') {
        lockPanel.classList.remove('d-none');
        contentPanel.classList.add('d-none');
    } else {
        lockPanel.classList.add('d-none');
        contentPanel.classList.remove('d-none');
    }
};

const renderCompletedResult = (item) => {
    //sostituisce il form con un messaggio finale quando la lezione e completata
    const resultArea = item.querySelector('.lesson-result-area');
    if (!resultArea) return;

    const quizForm = resultArea.querySelector('.education-quiz-form');
    const explanationForm = resultArea.querySelector('.education-explanation-form');

    if (quizForm) {
        resultArea.innerHTML = `
            <div class="alert alert-success border-0 shadow-sm mb-0">
                <i class="fas fa-check-circle me-2"></i>Quiz completato.
            </div>
        `;
        return;
    }

    if (explanationForm) {
        resultArea.innerHTML = `
            <button class="btn btn-outline-success" disabled>
                <i class="fas fa-check-circle me-1"></i> Lezione completata
            </button>
        `;
    }
};

const showLessonFeedback = (item, message, type = 'warning') => {
    //mostra il messaggio immediato dentro la lezione corrente
    const feedback = item.querySelector('.lesson-feedback');
    if (!feedback) return;

    feedback.innerHTML = '';

    const alert = document.createElement('div');
    alert.className = `alert alert-${normalizeAlertType(type)} border-0 shadow-sm`;
    const icon = document.createElement('i');
    icon.className = `${feedbackIcon(type)} me-2`;
    const text = document.createElement('span');
    text.textContent = message;

    alert.appendChild(icon);
    alert.appendChild(text);
    feedback.appendChild(alert);
};

const normalizeAlertType = (type) => {
    //limita i tipi alert a quelli bootstrap usati nella view
    if (['success', 'danger', 'warning', 'info'].includes(type)) return type;
    return 'warning';
};

const feedbackIcon = (type) => {
    //sceglie l'icona coerente con il tipo di feedback
    if (type === 'success') return 'fas fa-check-circle';
    if (type === 'danger') return 'fas fa-times-circle';
    if (type === 'info') return 'fas fa-info-circle';
    return 'fas fa-exclamation-triangle';
};

const openLessonCollapse = (item) => {
    //apre l'accordion della lezione appena sbloccata
    const collapse = item.querySelector('.accordion-collapse');
    if (!collapse || typeof bootstrap === 'undefined') return;

    bootstrap.Collapse.getOrCreateInstance(collapse, {
        toggle: false,
    }).show();
};
