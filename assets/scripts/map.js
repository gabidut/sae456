//  

const map = L.map('map').setView([48.99686841321466, -0.14098843345106538], 8);
const markers = {};
L.tileLayer('https://cartodb-basemaps-a.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

const colors = ['red', 'blue', 'green', 'orange', 'purple', 'cyan', 'magenta', 'yellow', 'brown', 'black'];

(() => {
    fetch('http://localhost/api/cities.php?citiesAndGPS=1').then(response => response.json())
        .then(cities => {
            cities.forEach(city => {
                const marker = L.marker([city.COM_LAT, city.COM_LONG]).addTo(map);
                marker.bindPopup(`<b>${city.COM_NOM}</b><br>Code INSEE: ${city.COM_CODE_INSEE}`);
                markers[city.COM_CODE_INSEE] = marker;
            });
        });

    fetch('http://localhost/api/cities.php?linesAndSteps=1').then(response => response.json())
        .then(lignes => {
            const lignesMap = {};
            lignes.forEach(ligne => {
                if (!lignesMap[ligne.LIGNE]) {
                    lignesMap[ligne.LIGNE] = {};
                }
                if (!lignesMap[ligne.LIGNE][ligne.DEPART]) {
                    lignesMap[ligne.LIGNE][ligne.DEPART] = [];
                }
                lignesMap[ligne.LIGNE][ligne.DEPART].push(ligne.ARRIVEE);
            });

            console.log(lignes);
            

            Object.keys(lignesMap).forEach(ligneNum => {
                const lineColor = colors[parseInt(ligneNum) % colors.length];
                const departures = lignesMap[ligneNum];
                Object.keys(departures).forEach(departCode => {
                    const departMarker = markers[departCode];
                    if (!departMarker) return;
                    const departLatLng = departMarker.getLatLng();
                    departures[departCode].forEach(arrivCode => {
                        const arrivMarker = markers[arrivCode];
                        if (!arrivMarker) return;
                        const arrivLatLng = arrivMarker.getLatLng();
                    
                        L.polyline([departLatLng, arrivLatLng], {color: lineColor}).addTo(map);
                    });
                });
            });
        });
})();