document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('priceChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const isin = canvas.getAttribute('data-isin');
    const currency = canvas.getAttribute('data-currency') || '€';
    const initialLabels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
    const initialValues = JSON.parse(canvas.getAttribute('data-values') || '[]');

    const priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initialLabels.length ? initialLabels : ['—'],
            datasets: [{
                label: `Prezzo (${currency})`,
                data: initialValues.length ? initialValues : [0],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'Prezzo: ' + context.parsed.y.toFixed(2) + ' ' + currency;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: 8,          //mostra max 8 etichette sulle ascisse
                        maxRotation: 0,
                        autoSkip: true,
                    }
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function (value) {
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    //aggiornamento grafico via AJAX
    const updateChart = (range) => {
        fetch(`/CompanyController/getChartDataJSON/${isin}/${range}`)
            .then(res => res.json())
            .then(data => {
                priceChart.data.labels = data.labels.length ? data.labels : ['—'];
                priceChart.data.datasets[0].data = data.values.length ? data.values : [0];
                priceChart.update();
            })
            .catch(err => console.error('Error fetching chart data:', err));
    };

    //listener per i bottoni di range
    document.querySelectorAll('.chart-range-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateChart(this.getAttribute('data-range'));
        });
    });
});
