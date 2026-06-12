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

const now = new Date();
now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
if (tripDate) {
    tripDate.value = now.toISOString().slice(0, 16);
    tripData.dateTime = tripDate.value;
}

function clearState(resetInputs = false) {
    highlightedPolylines.forEach(poly => map.removeLayer(poly));
    highlightedPolylines = [];

    polylines.forEach(poly => poly.setStyle({ color: 'grey', weight: 2 }));

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
        if (isDeparture) {
            const marker = markers[matchedCity.COM_CODE_INSEE]?.marker;
            if (marker) {
                marker.openPopup();
                map.setView(marker.getLatLng(), 10);
            }
        } else {
            const marker = markers[matchedCity.COM_CODE_INSEE]?.marker;
            if (marker) {
                marker.openPopup();
                map.setView(marker.getLatLng(), 10);
            }
        }
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
            marker.setIcon(L.icon({
                iconUrl: '/image/ville.png',
                iconSize: [35, 35],
                iconAnchor: [17.5, 25],
                popupAnchor: [1, -34],
            }));
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
                        duree: parseInt(ligne.DUREE) || 5,
                        horaires: ligne.HORAIRES ? ligne.HORAIRES.split(',') : []
                    };
                }

                const depM = markers[ligne.DEPART]?.marker;
                const arrM = markers[ligne.ARRIVEE]?.marker;
                if (depM && arrM) {
                    const poly = L.polyline([depM.getLatLng(), arrM.getLatLng()], {
                        color: '#2c2c2cff',
                        weight: 1.5,
                        opacity: 1
                    }).addTo(map);
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
        renderRouteList(timeRoute.path, "Le plus rapide", '#0681d3ff', stepsContainer);
        foundAny = true;
    } else {
        const noTimeDiv = document.createElement('div');
        noTimeDiv.innerHTML = '<span style="color:orange">Aucun itinéraire rapide trouvé (horaires incompatibles).</span>';
        stepsContainer.appendChild(noTimeDiv);
    }

    if (distRoute) {
        renderRouteList(distRoute.path, "Le moins cher ", '#236e14ff', stepsContainer);
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
    console.log(route);

    const routeDiv = document.createElement('div');
    routeDiv.style.flex = '1';

    let html = `<h3 style="color: ${colorParam};">${title}</h3>`;
    html += `<div>`;

    fetch('/api/reservation.php?simulateTripPrice=1&tripDepartureTime=' + (tripData.dateTime ? new Date(tripData.dateTime).toISOString() : ''), {
        method: 'POST',
        body: new URLSearchParams({
            simulateTripPrice: JSON.stringify(Object.values(route))
        })
    }).then(r => r.json()).then(data => {
        const priceP = document.createElement('p');
        priceP.innerHTML = `<b>Prix estimé :</b> ${data.prix} €`;
        routeDiv.insertBefore(priceP, routeDiv.querySelector('button'));

        Object.values(route).forEach((step, index) => {
            const departMarker = markers[step.departCode]?.marker;
            const arrivMarker = markers[step.arriveeCode]?.marker;

            if (departMarker && arrivMarker) {
                const poly = L.polyline([departMarker.getLatLng(), arrivMarker.getLatLng()], {
                    color: colorParam,
                    weight: 12,
                    opacity: 0.4,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                poly.bindPopup(`<b>${title} - Étape ${index + 1} (Ligne ${step.ligne})</b><br>${step.depart} → ${step.arrivee}`);
                highlightedPolylines.push(poly);

                html += `
                <p style="margin: 5px 0; font-size: 0.9em; color: #333;">
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
            displaySummaryPage(route, data.prix, title, colorParam);
        };
        routeDiv.appendChild(payButton);

    });


    container.appendChild(routeDiv);
}

function timeToMins(timeStr) {
    let [h, m] = timeStr.split(':').map(Number);
    return h * 60 + m;
}

function minsToTime(mins) {
    let h = Math.floor((mins % 1440) / 60).toString().padStart(2, '0');
    let m = Math.floor(mins % 60).toString().padStart(2, '0');
    return `${h}:${m}`;
}
function displaySummaryPage(route, price, title, colorParam) {
    const overlay = document.createElement('div');
    overlay.id = 'trip-summary-page';

    const startDate = tripData.dateTime ? new Date(tripData.dateTime).toLocaleString('fr-FR', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    }) : 'Immédiat';

    let html = `
        <div class="summary-container">
            <h1>Récapitulatif de votre trajet</h1>
            <hr>
            
            <div class="summary-header">
                <div class="summary-info">
                    <h3>${title}</h3>
                    <p><b>Départ :</b> ${startDate}</p>
                </div>
                <div class="summary-price">
                    <h2>Total : ${price} €</h2>
                </div>
            </div>

            <h3>Vos étapes</h3>
            <ul>
    `;

    Object.values(route).forEach((step, index) => {
        html += `
            <li>
                <b>Étape ${index + 1} - Ligne ${step.ligne}</b><br>
                <span>🚆 <b>${step.depart}</b> (${step.departTime}) ➔ <b>${step.arrivee}</b> (${step.arriveeTime})</span>
            </li>
        `;
    });

    html += `
            </ul>

            <div id="loyalty-root-auto"></div>
            
            <div class="summary-actions">
                <button id="btn-back-map">
                    ← Modifier l'itinéraire
                </button>
                <button id="btn-confirm-final">
                    Confirmer et Payer ✔
                </button>
            </div>
        </div>
    `;

    overlay.innerHTML = html;
    document.body.appendChild(overlay);

    const initialPrice = parseFloat(price) || 0;
    let selectedDiscount = 0;
    let selectedPoints = 0;

    // SECTION FIDÉLITÉ (Injectée dynamiquement)
    const pointsAvailable = window.userPoints || 0;
    if (pointsAvailable >= 100) {
        const loyaltyRoot = document.getElementById('loyalty-root-auto');
        loyaltyRoot.innerHTML = `
            <div class="loyalty-section">
                <div class="loyalty-header">
                    <h4>Utiliser mes points de fidélité</h4>
                    <span class="points-count">${pointsAvailable} pts disponibles</span>
                </div>
                <div class="points-options">
                    <div class="points-option ${pointsAvailable < 100 ? 'disabled' : ''}" data-pts="100" data-reduc="1">
                        <span class="pts">100 pts</span>
                        <span class="reduc">-1.00 €</span>
                    </div>
                    <div class="points-option ${pointsAvailable < 500 ? 'disabled' : ''}" data-pts="500" data-reduc="7">
                        <span class="pts">500 pts</span>
                        <span class="reduc">-7.00 €</span>
                    </div>
                    <div class="points-option ${pointsAvailable < 1000 ? 'disabled' : ''}" data-pts="1000" data-reduc="15">
                        <span class="pts">1000 pts</span>
                        <span class="reduc">-15.00 €</span>
                    </div>
                </div>
                <div class="price-summary">
                    <span class="original-price" style="display:none"></span>
                    <span class="final-price">Total : ${initialPrice.toFixed(2)} €</span>
                </div>
            </div>
        `;

        const options = loyaltyRoot.querySelectorAll('.points-option:not(.disabled)');
        const finalPriceSpan = loyaltyRoot.querySelector('.final-price');
        const originalPriceSpan = loyaltyRoot.querySelector('.original-price');
        const mainPriceDisplay = overlay.querySelector('.summary-price h2');

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                const wasSelected = opt.classList.contains('selected');
                options.forEach(o => o.classList.remove('selected'));

                if (wasSelected) {
                    selectedDiscount = 0;
                    selectedPoints = 0;
                    originalPriceSpan.style.display = 'none';
                } else {
                    opt.classList.add('selected');
                    selectedDiscount = parseFloat(opt.dataset.reduc);
                    selectedPoints = parseInt(opt.dataset.pts);
                    originalPriceSpan.style.display = 'inline';
                    originalPriceSpan.textContent = initialPrice.toFixed(2) + ' €';
                }
                const total = Math.max(0, initialPrice - selectedDiscount);
                finalPriceSpan.textContent = `Total : ${total.toFixed(2)} €`;
                if (mainPriceDisplay) mainPriceDisplay.textContent = `Total : ${total.toFixed(2)} €`;
            });
        });
    }

    document.getElementById('btn-back-map').onclick = () => {
        document.body.removeChild(overlay);
    };

    document.getElementById('btn-confirm-final').onclick = function () {
        this.textContent = 'Traitement en cours...';
        this.disabled = true;

        const reservationData = new FormData();
        reservationData.append('setTripDetails', JSON.stringify(
            Object.values(route).map(step => ({
                ligne: step.ligne,
                depart: step.depart,
                arrivee: step.arrivee,
                heure: step.departTime === "N/A" ? "00:00" : step.departTime
            }))
        ));

        if (selectedPoints > 0) {
            reservationData.append('pointsUsed', selectedPoints);
        }

        const firstStepTime = Object.values(route)[0]?.departTime || '';
        fetch(`/api/reservation.php?tripDepartureTime=${encodeURIComponent(firstStepTime)}`, {
            method: 'POST',
            body: reservationData
        }).then(response => {
            if (response.ok) {
                location.href = '/reservation/pay/';
            } else {
                throw new Error("Erreur serveur");
            }
        }).catch(err => {
            console.error(err);
            alert("Un problème est survenu lors de la confirmation.");
            this.textContent = 'Confirmer et Payer ✔';
            this.disabled = false;
        });
    };
}

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

            let currentMins = arrivalTimeAtNode[currNode];
            let nextDepMins = Infinity;

            if (edge.horaires && edge.horaires.length > 0) {
                let timeOfDay = currentMins % 1440;

                for (let h of edge.horaires) {
                    let hMins = timeToMins(h);
                    if (hMins >= timeOfDay) {
                        nextDepMins = (currentMins - timeOfDay) + hMins;
                        break;
                    }
                }

                if (nextDepMins === Infinity) {
                    let baseDays = currentMins - timeOfDay;
                    nextDepMins = baseDays + 1440 + timeToMins(edge.horaires[0]);
                }
            }

            let waitTime = 0;
            let expectedArrivalMins = currentMins;
            let usedDepartTime = "00:00";

            if (nextDepMins !== Infinity) {
                waitTime = nextDepMins - currentMins;
                expectedArrivalMins = nextDepMins + edge.duree;
                usedDepartTime = minsToTime(nextDepMins);
            } else {
                expectedArrivalMins = currentMins + edge.duree;
                usedDepartTime = minsToTime(currentMins);
            }

            let weight = Infinity;
            if (mode === 'distance') {
                weight = edge.distance;
            } else if (mode === 'time') {
                weight = waitTime + edge.duree;
            }

            let alt = distances[currNode] + weight;

            if (alt < distances[neighbor]) {
                distances[neighbor] = alt;
                arrivalTimeAtNode[neighbor] = expectedArrivalMins;
                previous[neighbor] = {
                    node: currNode,
                    edge: edge,
                    departTime: usedDepartTime,
                    arriveeTime: minsToTime(expectedArrivalMins)
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