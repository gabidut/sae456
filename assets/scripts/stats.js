document.addEventListener('DOMContentLoaded', function() {
    
    if (typeof vikingStatsData === 'undefined') {
        console.error("Erreur : Les données statistiques n'ont pas été chargées depuis PHP.");
        return;
    }

    const mapColors = {
        '1': '#E52B20', '2': '#88C62A', '3': '#8C187B', '4': '#3B8B41',
        '5': '#EBE015', '6': '#F3989B', '7': '#B07735', '8': '#9E9EA0',
        '9': '#2D9893', '10': '#5A3B22', '11': '#6C5599', '12': '#EDA11D',
        '13': '#121212', '14': '#8E2625', '15': '#ED1D24', '16': '#8E9091',
        '17': '#2E65A8', '18': '#F7EC20', '19': '#000000'
    };
    
    const backgroundColorsLignes = vikingStatsData.lignes.numeros.map(num => mapColors[num] || '#da1b23'); 

    const ctxLignes = document.getElementById('lignesChart');
    if (ctxLignes) {
        new Chart(ctxLignes.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: vikingStatsData.lignes.labels,
                datasets: [{
                    data: vikingStatsData.lignes.data,
                    backgroundColor: backgroundColorsLignes,
                    borderColor: '#141414',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#ffffff', font: { size: 12 } } },
                    tooltip: { callbacks: { label: function(c) { return ' ' + c.label + ' : ' + c.raw + '%'; } } }
                },
                cutout: '65%'
            }
        });
    }

    
    const ctxTopUsers = document.getElementById('topUsersChart');
    if (ctxTopUsers) {
        new Chart(ctxTopUsers.getContext('2d'), {
            type: 'bar',
            data: {
                labels: vikingStatsData.topUsers.labels,
                datasets: [{
                    label: 'Nombre de réservations',
                    data: vikingStatsData.topUsers.data,
                    backgroundColor: '#da1b23',
                    borderColor: '#ff4d4d',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#888888', stepSize: 1 },
                        grid: { color: '#222222' }
                    },
                    x: {
                        ticks: { color: '#ffffff', font: { weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});