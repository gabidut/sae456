//  
let isCreatingTripFromMap = false;
const tripData = {};

const map = L.map('map').setView([48.99686841321466, -0.14098843345106538], 8);
const markers = {};
let polylines = [];
let currentHighlightLayer = null;
L.tileLayer('https://cartodb-basemaps-a.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

document.addEventListener('keydown', function (event) {
    if ((event.key === 't' || event.key === 'T') && event.altKey) {
        isCreatingTripFromMap = !isCreatingTripFromMap;
        polylines.forEach(poly => poly.setStyle({ color: 'gray' }));
    }
});

const colors = ['red', 'blue', 'green', 'orange', 'purple', 'cyan', 'magenta', 'yellow', 'brown', 'black'];
const networkGraph = {};

(() => {
    fetch('/api/cities.php?citiesAndGPS=1').then(response => response.json())
        .then(cities => {
            cities.forEach(city => {
                const marker = L.marker([city.COM_LAT, city.COM_LONG]).addTo(map);
                marker.bindPopup(`<b>${city.COM_NOM}</b><br>Code INSEE: ${city.COM_CODE_INSEE}`);
                marker.on('click', function () {
                    if (isCreatingTripFromMap) {
                        if (!tripData.departure) {
                            tripData.departure = city.COM_CODE_INSEE;
                            console.log("Départ:", city.COM_NOM);
                        } else if (!tripData.arrival) {
                            tripData.arrival = city.COM_CODE_INSEE;
                            console.log("Arrivée:", city.COM_NOM);

                            const itinerary = calculateDijkstra(networkGraph, tripData.departure, tripData.arrival, markers);
                            console.log(JSON.stringify(itinerary, null, 2));

                            highlightRoute(itinerary);
                        }
                    }
                });
                // marker.on('mouseover', function () {
                //     if (isCreatingTripFromMap && tripData.departure) {
                //         if (currentHighlightLayer) {
                //             map.removeLayer(currentHighlightLayer);
                //         }
                //         const route = calculateDijkstra(networkGraph, tripData.departure, city.COM_CODE_INSEE, markers);
                //         currentHighlightLayer = highlightRoute(route);
                //     }
                // });


                markers[city.COM_CODE_INSEE] = { marker: marker, name: city.COM_NOM };
            });

            fetch('/api/cities.php?linesAndSteps=1').then(response => response.json())
                .then(lignes => {
                    const lignesMap = {};

                    Object.keys(markers).forEach(code => networkGraph[code] = {});

                    lignes.forEach(ligne => {
                        if (!lignesMap[ligne.LIGNE]) {
                            lignesMap[ligne.LIGNE] = {};
                        }
                        if (!lignesMap[ligne.LIGNE][ligne.DEPART]) {
                            lignesMap[ligne.LIGNE][ligne.DEPART] = [];
                        }
                        lignesMap[ligne.LIGNE][ligne.DEPART].push(ligne.ARRIVEE);

                        if (networkGraph[ligne.DEPART]) {
                            networkGraph[ligne.DEPART][ligne.ARRIVEE] = {
                                ligne: ligne.LIGNE,
                                weight: 1,
                                departTime: ligne.DEPART_TIME,
                                arriveeTime: ligne.ARRIVEE_TIME
                            };
                        }
                    });


                    Object.keys(lignesMap).forEach(ligneNum => {
                        const lineColor = colors[parseInt(ligneNum) % colors.length];
                        const departures = lignesMap[ligneNum];
                        Object.keys(departures).forEach(departCode => {
                            const departMarker = markers[departCode].marker;
                            if (!departMarker) return;
                            const departLatLng = departMarker.getLatLng();
                            departures[departCode].forEach(arrivCode => {
                                const arrivMarker = markers[arrivCode].marker;
                                if (!arrivMarker) return;
                                const arrivLatLng = arrivMarker.getLatLng();

                                const poly = L.polyline([departLatLng, arrivLatLng], { color: lineColor, weight: 8 }).addTo(map);
                                poly.bindPopup(`Ligne ${ligneNum}:<br> ${markers[departCode]?.name} → ${markers[arrivCode]?.name}`);
                                polylines.push(poly);
                            });
                        });
                    });
                });
        });
})();

function highlightRoute(route) {

    Object.values(route).forEach((step, index) => {
        const departMarker = markers[step.departCode]?.marker;
        const arrivMarker = markers[step.arriveeCode]?.marker;

        if (departMarker && arrivMarker) {
            const departLatLng = departMarker.getLatLng();
            const arrivLatLng = arrivMarker.getLatLng();

            const poly = L.polyline([departLatLng, arrivLatLng], { color: 'red', weight: 10 }).addTo(map);
            poly.bindPopup(`<b>Étape ${index + 1} (Ligne ${step.ligne})</b><br>${step.depart} → ${step.arrivee}`);
        } else {
            console.warn(`Impossible de tracer l'étape ${index + 1}. Marqueurs introuvables pour :`, step);
        }
    });
}

function calculateDijkstra(graph, startCode, endCode, markers) {
    const distances = {};
    const previous = {};
    const unvisited = new Set(Object.keys(graph));

    for (let node of unvisited) {
        distances[node] = Infinity;
        previous[node] = null;
    }
    distances[startCode] = 0;

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
            let weight = graph[currNode][neighbor].weight;
            let alt = distances[currNode] + weight;

            if (alt < distances[neighbor]) {
                distances[neighbor] = alt;
                previous[neighbor] = {
                    node: currNode,
                    edge: graph[currNode][neighbor]
                };
            }
        }
    }

    const path = [];
    let u = endCode;

    if (previous[u] === null && u !== startCode) {
        return null;
    }

    while (previous[u]) {
        path.unshift({
            arriveeCode: u,
            departCode: previous[u].node,
            ligne: previous[u].edge.ligne,
            departTime: previous[u].edge.departTime || "14:03",
            arriveeTime: previous[u].edge.arriveeTime || "15:45"
        });
        u = previous[u].node;
    }

    const formattedResult = {};
    path.forEach((step, index) => {
        formattedResult[index.toString()] = {
            "ligne": step.ligne,
            "depart": markers[step.departCode] ? markers[step.departCode].name : step.departCode,
            "departCode": step.departCode,
            "departTime": step.departTime,
            "arrivee": markers[step.arriveeCode] ? markers[step.arriveeCode].name : step.arriveeCode,
            "arriveeCode": step.arriveeCode,
            "arriveeTime": step.arriveeTime
        };
    });

    return formattedResult;
}