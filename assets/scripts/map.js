//  

const map = L.map('map').setView([51.505, -0.09], 13);
const markers = {};
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

(() => {
    fetch('http://localhost/api/cities.php?citiesAndGPS=1').then(response => response.json())
        .then(cities => {
            cities.forEach(city => {
                const marker = L.marker([city.COM_LAT, city.COM_LONG]).addTo(map);
                marker.bindPopup(`<b>${city.COM_NOM}</b><br>Code INSEE: ${city.COM_CODE_INSEE}`);
                markers[city.COM_CODE_INSEE] = marker;
            });
        });

        
})();