let isCreatingTripFromMap = true;
const tripData = { departure: null, arrival: null, dateTime: null };
const map = L.map('map').setView([48.99686841321466, -0.14098843345106538], 8);
const markers = {};
let polylines = [];
let highlightedPolylines = [];
let gcities = {};

L.tileLayer('https://cartodb-basemaps-a.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

const departure = document.getElementById('ville1');
const arrival = document.getElementById('ville2');
const tripDate = document.getElementById('trip-date');

// Initialiser à la date/heure actuelle
const now = new Date();
now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
if (tripDate) {
    tripDate.value = now.toISOString().slice(0, 16);
    tripData.dateTime = tripDate.value;
}

function clearState(resetInputs = false) {
    // Nettoie les lignes colorées des résultats
    highlightedPolylines.forEach(poly => map.removeLayer(poly));
    highlightedPolylines = [];

    // Remet les lignes de base en gris
    polylines.forEach(poly => poly.setStyle({ color: 'grey', weight: 8 }));

    const stepsContainer = document.getElementById('steps');
    if (stepsContainer) stepsContainer.innerHTML = '';

    if (resetInputs) {
        tripData.departure = null;
        tripData.arrival = null;
        if (departure) departure.value = '';
        if (arrival) arrival.value = '';
    }
}

function updateCityInput(e) {
    clearState();
    const isDeparture = e.target.id === 'ville1';
    const matchedCity = gcities[e.target.value.toLowerCase()];

    if (matchedCity) {
        tripData[isDeparture ? 'departure' : 'arrival'] = matchedCity.COM_CODE_INSEE;
        if (tripData.departure && tripData.arrival) calculateAndSuggestRoutes();
    } else {
        tripData[isDeparture ? 'departure' : 'arrival'] = null;
    }
}

if (departure) departure.addEventListener('input', updateCityInput);
if (arrival) arrival.addEventListener('input', updateCityInput);
if (tripDate) tripDate.addEventListener('change', function () {
    clearState();
    tripData.dateTime = this.value;
    if (tripData.departure && tripData.arrival) calculateAndSuggestRoutes();
});

const colors = ['red', 'blue', 'green', 'orange', 'purple', 'cyan', 'magenta', 'yellow', 'brown', 'black'];
const networkGraph = {};

// Initialisation de la carte et du graphe
(() => {
    fetch('/api/cities.php?citiesAndGPS=1').then(r => r.json()).then(cities => {
        const datalist = document.createElement('datalist');
        datalist.id = 'cities';
        document.body.appendChild(datalist);

        gcities = cities.reduce((acc, city) => {
            acc[city.COM_NOM.toLowerCase()] = city;
            datalist.appendChild(new Option(city.COM_NOM, city.COM_NOM));
            return acc;
        }, {});

        cities.forEach(city => {
            const marker = L.marker([city.COM_LAT, city.COM_LONG]).addTo(map);
            marker.bindPopup(`<b>${city.COM_NOM}</b>`);
            marker.on('click', () => handleMarkerClick(city));
            markers[city.COM_CODE_INSEE] = { marker, name: city.COM_NOM };
        });

        fetch('/api/cities.php?linesAndSteps=1').then(r => r.json()).then(lignes => {
            Object.keys(markers).forEach(code => networkGraph[code] = {});

            lignes.forEach(ligne => {
                if (networkGraph[ligne.DEPART]) {
                    networkGraph[ligne.DEPART][ligne.ARRIVEE] = {
                        ligne: ligne.LIGNE,
                        distance: parseFloat(ligne.DISTANCE ? ligne.DISTANCE.replace(',', '.') : 0) || 0,
                        duree: parseInt(ligne.DUREE) || 5, // 5 min par défaut
                        horaires: ligne.HORAIRES ? ligne.HORAIRES.split(',') : []
                    };
                }

                const depM = markers[ligne.DEPART]?.marker;
                const arrM = markers[ligne.ARRIVEE]?.marker;
                if (depM && arrM) {
                    const poly = L.polyline([depM.getLatLng(), arrM.getLatLng()], { color: 'grey', weight: 8 }).addTo(map);
                    polylines.push(poly);
                }
            });
        });
    });
})();

function handleMarkerClick(city) {
    if (!isCreatingTripFromMap) return;
    if (tripData.departure && tripData.arrival) clearState(true);

    if (!tripData.departure) {
        tripData.departure = city.COM_CODE_INSEE;
        if (departure) departure.value = city.COM_NOM;
    } else if (!tripData.arrival) {
        tripData.arrival = city.COM_CODE_INSEE;
        if (arrival) arrival.value = city.COM_NOM;
        calculateAndSuggestRoutes();
    }
}

function calculateAndSuggestRoutes() {
    const stepsContainer = document.getElementById('steps');
    if (!stepsContainer) return;

    stepsContainer.innerHTML = '<i>Calcul en cours...</i>';

    const timeRoute = calculateDijkstra(networkGraph, tripData.departure, tripData.arrival, 'time');
    const distRoute = calculateDijkstra(networkGraph, tripData.departure, tripData.arrival, 'distance');

    clearState();
    stepsContainer.innerHTML = '';

    let foundAny = false;

    if (timeRoute) {
        renderRouteList(timeRoute.path, "⏳ Le plus rapide", '#28a745', stepsContainer);
        foundAny = true;
    } else {
        const noTimeDiv = document.createElement('div');
        noTimeDiv.innerHTML = '<span style="color:orange">Aucun itinéraire rapide trouvé (horaires incompatibles).</span>';
        stepsContainer.appendChild(noTimeDiv);
    }

    if (distRoute) {
        renderRouteList(distRoute.path, "💸 Le moins cher (Distance)", '#007bff', stepsContainer);
        foundAny = true;
    } else {
        const noDistDiv = document.createElement('div');
        noDistDiv.innerHTML = '<span style="color:orange">Aucun itinéraire économique trouvé.</span>';
        stepsContainer.appendChild(noDistDiv);
    }

    if (!foundAny) {
        stepsContainer.innerHTML = '<span style="color:red">Aucun itinéraire trouvé pour ces critères.</span>';
    }
}

function renderRouteList(route, title, colorParam, container) {
    const routeDiv = document.createElement('div');
    routeDiv.style.flex = '1';
    routeDiv.style.border = `2px solid ${colorParam}`;
    routeDiv.style.borderRadius = '8px';
    routeDiv.style.padding = '15px';
    routeDiv.style.backgroundColor = '#fdfdfd';

    let html = `<h3 style="color: ${colorParam}; margin-top: 0;">${title}</h3>`;
    html += `<div style="margin-bottom: 15px;">`;

    Object.values(route).forEach((step, index) => {
        const departMarker = markers[step.departCode]?.marker;
        const arrivMarker = markers[step.arriveeCode]?.marker;

        if (departMarker && arrivMarker) {
            // opacity: 0.6 permet de voir les tracés s'ils se superposent
            const poly = L.polyline([departMarker.getLatLng(), arrivMarker.getLatLng()], {
                color: colorParam,
                weight: 8,
                opacity: 0.6
            }).addTo(map);

            poly.bindPopup(`<b>${title} - Étape ${index + 1} (Ligne ${step.ligne})</b><br>${step.depart} → ${step.arrivee}`);
            highlightedPolylines.push(poly);

            html += `
                <p style="margin: 5px 0; font-size: 0.9em;">
                    <b>Étape ${index + 1} (Ligne ${step.ligne})</b><br>
                    ${step.depart} (${step.departTime}) ➔ ${step.arrivee} (${step.arriveeTime})
                </p>`;
        }
    });

    html += `</div>`;
    routeDiv.innerHTML = html;

    const payButton = document.createElement('button');
    payButton.textContent = 'Confirmer et Payer';
    payButton.classList.add('btn-pay');
    payButton.style.width = '100%';
    payButton.style.padding = '10px';
    payButton.style.backgroundColor = colorParam;
    payButton.style.color = 'white';
    payButton.style.border = 'none';
    payButton.style.borderRadius = '4px';
    payButton.style.cursor = 'pointer';

    payButton.onclick = () => {
        const reservationData = new FormData();
        reservationData.append('setTripDetails', JSON.stringify(
            Object.values(route).map(step => ({
                ligne: step.ligne,
                depart: step.depart,
                arrivee: step.arrivee,
                heure: step.departTime === "N/A" ? "00:00" : step.departTime
            }))
        ));

        fetch('/api/reservation.php', {
            method: 'POST',
            body: new URLSearchParams(reservationData)
        }).then(response => {
            if (response.ok) location.href = '/reservation/pay/';
        });
    };

    routeDiv.appendChild(payButton);
    container.appendChild(routeDiv);
}

// Utilitaires de conversion pour le temps
function timeToMins(timeStr) {
    let [h, m] = timeStr.split(':').map(Number);
    return h * 60 + m;
}

function minsToTime(mins) {
    let h = Math.floor((mins % 1440) / 60).toString().padStart(2, '0');
    let m = Math.floor(mins % 60).toString().padStart(2, '0');
    return `${h}:${m}`;
}

// Algorithme de Dijkstra mis à jour (Temps et Distance)
function calculateDijkstra(graph, startCode, endCode, mode) {
    const distances = {};
    const previous = {};
    const arrivalTimeAtNode = {};
    const unvisited = new Set(Object.keys(graph));

    let startMins = 0;
    if (tripData.dateTime) {
        const dateObj = new Date(tripData.dateTime);
        startMins = dateObj.getHours() * 60 + dateObj.getMinutes();
    }

    for (let node of unvisited) {
        distances[node] = Infinity;
        previous[node] = null;
        arrivalTimeAtNode[node] = Infinity;
    }

    distances[startCode] = 0;
    arrivalTimeAtNode[startCode] = startMins;

    while (unvisited.size > 0) {
        let currNode = null;
        for (let node of unvisited) {
            if (currNode === null || distances[node] < distances[currNode]) {
                currNode = node;
            }
        }

        if (distances[currNode] === Infinity || currNode === endCode) break;
        unvisited.delete(currNode);

        for (let neighbor in graph[currNode]) {
            let edge = graph[currNode][neighbor];
            let weight = Infinity;
            let expectedArrivalMins = arrivalTimeAtNode[currNode];
            let usedDepartTime = "00:00";

            if (mode === 'distance') {
                weight = edge.distance;
            } else if (mode === 'time') {
                let currentMins = arrivalTimeAtNode[currNode];
                let nextDepMins = Infinity;

                for (let h of edge.horaires) {
                    let hMins = timeToMins(h);
                    if (hMins >= currentMins) {
                        nextDepMins = hMins;
                        break;
                    }
                }

                if (nextDepMins !== Infinity) {
                    let waitTime = nextDepMins - currentMins;
                    weight = waitTime + edge.duree;
                    expectedArrivalMins = nextDepMins + edge.duree;
                    usedDepartTime = minsToTime(nextDepMins);
                }
            }

            let alt = distances[currNode] + weight;

            if (alt < distances[neighbor]) {
                distances[neighbor] = alt;
                arrivalTimeAtNode[neighbor] = mode === 'time' ? expectedArrivalMins : 0;
                previous[neighbor] = {
                    node: currNode,
                    edge: edge,
                    departTime: mode === 'time' ? usedDepartTime : "N/A",
                    arriveeTime: mode === 'time' ? minsToTime(expectedArrivalMins) : "N/A"
                };
            }
        }
    }

    if (previous[endCode] === null) return null;

    const path = {};
    let u = endCode;
    let tempPath = [];

    while (previous[u]) {
        tempPath.unshift({
            arriveeCode: u,
            departCode: previous[u].node,
            ligne: previous[u].edge.ligne,
            departTime: previous[u].departTime,
            arriveeTime: previous[u].arriveeTime,
            depart: markers[previous[u].node]?.name || previous[u].node,
            arrivee: markers[u]?.name || u
        });
        u = previous[u].node;
    }

    tempPath.forEach((step, i) => path[i.toString()] = step);
    return { path, totalWeight: distances[endCode] };
}