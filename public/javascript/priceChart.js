document.addEventListener('DOMContentLoaded', () => {
    //canvas del grafico
    const canvas = document.getElementById('priceChart');
    if (!canvas) return;


    //ottiene il contesto di rendering 2d per disegnare sul canvas
    const ctx = canvas.getContext('2d');
    //isin da data-attribute del canvas
    const isin = canvas.getAttribute('data-isin');
    //recupera valuta
    const currency = canvas.getAttribute('data-currency') || '€';
    //valori ed etichette inizali passati in json
    const initialLabels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
    const initialValues = JSON.parse(canvas.getAttribute('data-values') || '[]');


    // inizializza nuovo grafico
    const priceChart = new Chart(ctx, {
        type: 'line',
        //configura i dati del grafico
        data: {
            //imposta le etichette o un trattino se l'array è vuoto
            labels: initialLabels.length ? initialLabels : ['—'],
            //definisce il set di dati da visualizzare
            datasets: [{
                //etichetta mostrata nella legenda o nel tooltip
                label: `Prezzo (${currency})`,
                //imposta i valori dei dati o zero se l'array è vuoto
                data: initialValues.length ? initialValues : [0],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.1,
                fill: true
            }]
        },
        //opzioni di configurazione del comportamento del grafico
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                //permette l'interazione anche se non si tocca esattamente il punto
                intersect: false,
                //mostra i dati basandosi sull'indice dell'asse x
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                //personalizza il comportamento del tooltip al passaggio del mouse
                tooltip: {
                    callbacks: {
                        //funzione per formattare il testo del tooltip
                        label: function (context) {
                            //prezzo formattato con due decimali e la valuta
                            return 'Prezzo: ' + context.parsed.y.toFixed(2) + ' ' + currency;
                        }
                    }
                }
            },
            //assi del grafico
            scales: {
                //asse x
                x: {
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: 8,
                        maxRotation: 0,
                        autoSkip: true,
                    }
                },
                //asse y
                y: {

                    beginAtZero: false,
                    ticks: {
                        //funzione per formattare i numeri sull'asse y
                        callback: function (value) {
                            //mostra il valore con due cifre decimali
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    //variazione percentuale
    const variationSpan = document.getElementById('chartVariation');

    //calcolo
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

        //formattazione risultato
        variationSpan.textContent = `${sign}${diff.toFixed(2)}%`;
        variationSpan.className = `ms-2 small ${color} fw-bold`;
    };

    updateVariation(initialValues);

    //aggiornamento grafico in base a timeline con ajax
    const updateChart = (range) => {
        //endp
        fetch(`/CompanyController/getChartDataJSON/${isin}/${range}`)
            .then(res => res.json())
            .then(data => {
                //aggiorna il grafico con i nuovi dati
                priceChart.data.labels = data.labels.length ? data.labels : ['—'];
                priceChart.data.datasets[0].data = data.values.length ? data.values : [0];
                priceChart.update();
                updateVariation(data.values);
            })
            .catch(err => console.error('Error fetching chart data:', err));
    };

    //seleziona pulsanti timelin
    document.querySelectorAll('.chart-range-btn').forEach(btn => {
        //al click viene settato ad attivo e cambiato grafico
        btn.addEventListener('click', function () {
            document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateChart(this.getAttribute('data-range'));
        });
    });
});