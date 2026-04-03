// attende che il dom sia completamente caricato prima di eseguire lo script
document.addEventListener('DOMContentLoaded', () => {
    // recupera l'elemento canvas tramite il suo id
    const canvas = document.getElementById('priceChart');
    // se l'elemento non esiste interrompe l'esecuzione
    if (!canvas) return;


    // ottiene il contesto di rendering 2d per disegnare sul canvas
    const ctx = canvas.getContext('2d');
    // recupera il codice isin dagli attributi data del canvas
    const isin = canvas.getAttribute('data-isin');
    // recupera la valuta o imposta l'euro come valore predefinito
    const currency = canvas.getAttribute('data-currency') || '€';
    // analizza le etichette iniziali passate come stringa json
    const initialLabels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
    // analizza i valori iniziali passati come stringa json
    const initialValues = JSON.parse(canvas.getAttribute('data-values') || '[]');


    // inizializza un nuovo grafico di tipo chart.js
    const priceChart = new Chart(ctx, {
        // definisce il grafico come grafico a linee
        type: 'line',
        // configura i dati del grafico
        data: {
            // imposta le etichette o un trattino se l'array è vuoto
            labels: initialLabels.length ? initialLabels : ['—'],
            // definisce il set di dati da visualizzare
            datasets: [{
                // etichetta mostrata nella legenda o nel tooltip
                label: `Prezzo (${currency})`,
                // imposta i valori dei dati o zero se l'array è vuoto
                data: initialValues.length ? initialValues : [0],
                // colore della linea del grafico
                borderColor: '#0d6efd',
                // colore dell'area di riempimento sotto la linea
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                // spessore della linea del grafico
                borderWidth: 2,
                // raggio dei punti (impostato a zero per non mostrarli)
                pointRadius: 0,
                // curvatura della linea (0.1 per una leggera smussatura)
                tension: 0.1,
                // abilita il riempimento sotto la linea
                fill: true
            }]
        },
        // opzioni di configurazione del comportamento del grafico
        options: {
            // rende il grafico adattabile alle dimensioni del contenitore
            responsive: true,
            // mantiene il rapporto d'aspetto originale
            maintainAspectRatio: true,
            // configura le opzioni di interazione dell'utente
            interaction: {
                // permette l'interazione anche se non si tocca esattamente il punto
                intersect: false,
                // mostra i dati basandosi sull'indice dell'asse x
                mode: 'index',
            },
            // configurazione dei plugin aggiuntivi
            plugins: {
                // nasconde la legenda superiore
                legend: { display: false },
                // personalizza il comportamento del tooltip al passaggio del mouse
                tooltip: {
                    callbacks: {
                        // funzione per formattare il testo del tooltip
                        label: function (context) {
                            // restituisce il prezzo formattato con due decimali e la valuta
                            return 'Prezzo: ' + context.parsed.y.toFixed(2) + ' ' + currency;
                        }
                    }
                }
            },
            // configurazione degli assi del grafico
            scales: {
                // impostazioni dell'asse orizzontale
                x: {
                    // nasconde la griglia verticale
                    grid: { display: false },
                    // impostazioni delle etichette sull'asse x
                    ticks: {
                        // limita a un massimo di 8 etichette visibili
                        maxTicksLimit: 8,
                        // impedisce la rotazione delle etichette
                        maxRotation: 0,
                        // salta automaticamente le etichette per evitare sovrapposizioni
                        autoSkip: true,
                    }
                },
                // impostazioni dell'asse verticale
                y: {
                    // non forza l'asse a partire necessariamente da zero
                    beginAtZero: false,
                    // impostazioni delle etichette sull'asse y
                    ticks: {
                        // funzione per formattare i numeri sull'asse y
                        callback: function (value) {
                            // mostra il valore con due cifre decimali
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // seleziona l'elemento per mostrare la variazione percentuale
    const variationSpan = document.getElementById('chartVariation');

    // funzione per calcolare e mostrare la variazione percentuale
    const updateVariation = (values) => {
        if (!variationSpan || values.length < 2) {
            if (variationSpan) variationSpan.textContent = '';
            return;
        }

        const first = values[0];
        const last = values[values.length - 1];

        if (first === 0) {
            variationSpan.textContent = '';
            return;
        }

        const diff = ((last - first) / first) * 100;
        const color = diff >= 0 ? 'text-success' : 'text-danger';
        const sign = diff >= 0 ? '+' : '';

        variationSpan.textContent = `${sign}${diff.toFixed(2)}%`;
        variationSpan.className = `ms-2 small ${color} fw-bold`;
    };

    // inizializza la variazione con i dati predefiniti
    updateVariation(initialValues);

    // definisce la funzione per aggiornare il grafico tramite chiamata ajax
    const updateChart = (range) => {
        // esegue una richiesta fetch all'endpoint specifico per il range scelto
        fetch(`/CompanyController/getChartDataJSON/${isin}/${range}`)
            // converte la risposta ricevuta in formato json
            .then(res => res.json())
            // gestisce i dati ricevuti
            .then(data => {
                // aggiorna le etichette del grafico con i nuovi dati
                priceChart.data.labels = data.labels.length ? data.labels : ['—'];
                // aggiorna i valori del set di dati principale
                priceChart.data.datasets[0].data = data.values.length ? data.values : [0];
                // applica le modifiche e ridisegna il grafico
                priceChart.update();
                // aggiorna la variazione percentuale
                updateVariation(data.values);
            })
            // cattura e logga eventuali errori durante la richiesta
            .catch(err => console.error('Error fetching chart data:', err));
    };

    // seleziona tutti i pulsanti per il cambio del range temporale
    document.querySelectorAll('.chart-range-btn').forEach(btn => {
        // aggiunge un ascoltatore per l'evento click su ogni pulsante
        btn.addEventListener('click', function () {
            // rimuove la classe active da tutti i pulsanti per resettare lo stile
            document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove('active'));
            // aggiunge la classe active solo al pulsante appena cliccato
            this.classList.add('active');
            // chiama la funzione di aggiornamento con il valore del range del pulsante
            updateChart(this.getAttribute('data-range'));
        });
    });
});