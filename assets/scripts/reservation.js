let steps = {};
let lignes = [];
let scheduleCache = {};

document.getElementById('date-depart').value = new Date().toISOString().split('T')[0];

async function fetchLignes() {
    return new Promise(async (resolve, reject) => {
        const response = await fetch('/api/reservation.php?lignes');
        if (!response.ok) {
            reject('Erreur lors du chargement des lignes');
            return;
        }
        lignes = await response.json();
        resolve();
    });
}

function addStep() {
    document.querySelectorAll('.btn-remove-step').forEach(btn => btn.style.display = 'none');

    const stepIndex = Object.keys(steps).length;
    steps[stepIndex] = { ligne: '', depart: '', arrivee: '', departTime: '', arriveeTime: '' };

    const div = document.createElement('div');
    div.classList.add('search-form-horizontal');
    div.dataset.step = stepIndex;

    // -- LINE GROUP --
    const ligneGroup = document.createElement('div');
    ligneGroup.classList.add('input-group');
    const ligneLabel = document.createElement('label');
    ligneLabel.textContent = 'Ligne';

    const ligneSelect = document.createElement('select');
    ligneSelect.innerHTML = '<option value="">Choisir une ligne</option>';
    ligneSelect.required = true;

    // -- DEPART GROUP --
    const departGroup = document.createElement('div');
    departGroup.classList.add('input-group');
    const departLabel = document.createElement('label');
    departLabel.textContent = 'Départ';

    const departSelect = document.createElement('select');
    departSelect.id = `depart-input-${stepIndex}`;
    departSelect.innerHTML = '<option value="">D\'où partez-vous ?</option>';
    departSelect.disabled = true;

    const departTimeSelect = document.createElement('select');
    departTimeSelect.id = `depart-time-${stepIndex}`;
    departTimeSelect.innerHTML = '<option value="">Heure</option>';
    departTimeSelect.disabled = true;

    // -- ARRIVEE GROUP --
    const arriveeGroup = document.createElement('div');
    arriveeGroup.classList.add('input-group');
    const arriveeLabel = document.createElement('label');
    arriveeLabel.textContent = 'Arrivée';

    const arriveeSelect = document.createElement('select');
    arriveeSelect.innerHTML = '<option value="">Où allez-vous ?</option>';
    arriveeSelect.disabled = true;

    const arriveeTimeSelect = document.createElement('select');
    arriveeTimeSelect.id = `arrivee-time-${stepIndex}`;
    arriveeTimeSelect.innerHTML = '<option value="">Heure</option>';
    arriveeTimeSelect.disabled = true;

    if (stepIndex > 0) {
        const previousArrivee = steps[stepIndex - 1].arrivee;
        if (previousArrivee) {
            departSelect.innerHTML = `<option value="${previousArrivee}" selected>${previousArrivee}</option>`;
            departSelect.disabled = true;
            steps[stepIndex].depart = previousArrivee;
            computeLineForStep(stepIndex, ligneSelect);
        }
    } else {
        lignes.forEach(ligne => {
            const option = document.createElement('option');
            option.value = ligne.LIG_NUM;
            option.textContent = ligne.LIG_NUM;
            ligneSelect.appendChild(option);
        });
    }

    ligneGroup.append(ligneLabel, ligneSelect);
    departGroup.append(departLabel, departSelect, departTimeSelect);
    arriveeGroup.append(arriveeLabel, arriveeSelect, arriveeTimeSelect);
    div.append(ligneGroup, buildDivider(), departGroup, buildDivider(), arriveeGroup);

    const stepWrapper = document.createElement('div');
    stepWrapper.classList.add('step-wrapper');
    stepWrapper.dataset.wrapperStep = stepIndex;
    stepWrapper.appendChild(div);

    if (stepIndex > 0) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.classList.add('btn-remove-step');
        removeBtn.innerHTML = '&times;';
        removeBtn.title = 'Supprimer cette étape';
        removeBtn.addEventListener('click', () => {
            stepWrapper.remove();
            delete steps[stepIndex];
            triggerValidationAndPrice();

            const keys = Object.keys(steps).map(Number).sort((a, b) => a - b);
            if (keys.length > 1) {
                const lastKey = keys[keys.length - 1];
                const lastWrapper = document.querySelector(`.step-wrapper[data-wrapper-step="${lastKey}"]`);
                if (lastWrapper) {
                    const lastBtn = lastWrapper.querySelector('.btn-remove-step');
                    if (lastBtn) lastBtn.style.display = 'flex';
                }
            }
        });
        stepWrapper.appendChild(removeBtn);
    }

    document.getElementById('steps').appendChild(stepWrapper);

    const filterStops = () => {
        const cache = scheduleCache[stepIndex];
        if (!cache) return;

        const orderedVilles = cache.orderedVilles;
        const currentDep = steps[stepIndex].depart || departSelect.value;
        const currentArr = steps[stepIndex].arrivee || arriveeSelect.value;

        // 1. Villes disponibles pour le départ (tout ce qui est AVANT l'arrivée sélectionnée)
        let depOptions = orderedVilles;
        if (currentArr) {
            const arrIdx = orderedVilles.indexOf(currentArr);
            depOptions = orderedVilles.slice(0, arrIdx);
        }

        // 2. Villes disponibles pour l'arrivée (tout ce qui est APRÈS le départ sélectionné)
        let arrOptions = orderedVilles;
        if (currentDep) {
            const depIdx = orderedVilles.indexOf(currentDep);
            arrOptions = orderedVilles.slice(depIdx + 1);
        }

        // Mise à jour du Select Départ (uniquement si ce n'est pas une étape forcée)
        if (stepIndex === 0) {
            populateSelectOptions(departSelect, depOptions, "D'où partez-vous ?");
            if (currentDep && depOptions.includes(currentDep)) {
                departSelect.value = currentDep;
            }
        }

        // Mise à jour du Select Arrivée
        populateSelectOptions(arriveeSelect, arrOptions, "Où allez-vous ?");
        if (currentArr && arrOptions.includes(currentArr)) {
            arriveeSelect.value = currentArr;
        }

        triggerValidationAndPrice();
    };

    const updateTimes = async () => {
        const cache = scheduleCache[stepIndex];
        if (!cache) return;

        const depVal = departSelect.value;
        const arrVal = arriveeSelect.value;

        if (depVal) {
            const currentSelectedTime = departTimeSelect.value;
            const prevArrTime = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
            populateTimeSelect(departTimeSelect, cache.map[depVal] || [], prevArrTime);
            departTimeSelect.disabled = false;

            if (currentSelectedTime && Array.from(departTimeSelect.options).some(o => o.value === currentSelectedTime)) {
                departTimeSelect.value = currentSelectedTime;
            }
        } else {
            departTimeSelect.innerHTML = '<option value="">Heure</option>';
            departTimeSelect.disabled = true;
        }

        if (depVal && arrVal && departTimeSelect.value) {
            try {
                const response = await fetch(`/api/reservation.php?getFinalHoraire=1&lineId=${steps[stepIndex].ligne}&codeInseeDepart=${encodeURIComponent(depVal)}&codeInseeArrivee=${encodeURIComponent(arrVal)}&horaireDepart=${encodeURIComponent(departTimeSelect.value)}`);
                const data = await response.json();

                if (data && data.horaires && data.horaires.length > 0) {
                    arriveeTimeSelect.innerHTML = `<option value="${data.horaires[0]}">${data.horaires[0]}</option>`;
                    steps[stepIndex].arriveeTime = data.horaires[0];
                    arriveeTimeSelect.disabled = false;
                } else {
                    arriveeTimeSelect.innerHTML = '<option value="">Indisponible</option>';
                    steps[stepIndex].arriveeTime = '';
                }
            } catch (e) {
                console.error("Erreur calcul horaire: ", e);
            }
        } else {
            arriveeTimeSelect.innerHTML = '<option value="">Heure</option>';
            steps[stepIndex].arriveeTime = '';
            arriveeTimeSelect.disabled = true;
        }
        triggerValidationAndPrice();
    };

    ligneSelect.addEventListener('change', async function () {
        const ligneValue = this.value;
        steps[stepIndex].ligne = ligneValue;

        steps[stepIndex].arrivee = '';
        if (stepIndex === 0) steps[stepIndex].depart = '';

        removeSubsequentSteps(stepIndex);

        if (ligneValue) {
            const response = await fetch(`/api/reservation.php?ligne=${encodeURIComponent(ligneValue)}`);
            const data = await response.json();

            const map = {};
            const orderedVilles = [];

            data.forEach(item => {
                const ville = item.VILLE_ARRET;
                const time = item.HEURE_PASSAGE;
                if (!map[ville]) {
                    map[ville] = [];
                    orderedVilles.push(ville);
                }
                if (time && !map[ville].includes(time)) map[ville].push(time);
            });

            scheduleCache[stepIndex] = { map, orderedVilles };

            if (stepIndex === 0) departSelect.disabled = false;
            arriveeSelect.disabled = false;

            filterStops();
            updateTimes();
        } else {
            departSelect.disabled = true;
            arriveeSelect.disabled = true;
            departTimeSelect.disabled = true;
            arriveeTimeSelect.disabled = true;
        }
    });

    departSelect.addEventListener('change', function () {
        steps[stepIndex].depart = this.value;

        removeSubsequentSteps(stepIndex);

        filterStops();
        updateTimes();
    });

    arriveeSelect.addEventListener('change', function () {
        steps[stepIndex].arrivee = this.value;

        removeSubsequentSteps(stepIndex);

        filterStops();
        updateTimes();
    });

    departTimeSelect.addEventListener('change', function () {
        steps[stepIndex].departTime = this.value;

        removeSubsequentSteps(stepIndex);

        updateTimes();
    });
}

function buildDivider() {
    const divider = document.createElement('div');
    divider.classList.add('divider');
    return divider;
}

function populateSelectOptions(selectObj, optionsArr, placeholder) {
    selectObj.innerHTML = `<option value="">${placeholder}</option>`;
    optionsArr.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt;
        option.textContent = opt;
        selectObj.appendChild(option);
    });
}

function populateTimeSelect(selectElement, times, minTime) {
    selectElement.innerHTML = '<option value="">Heure</option>';
    times.sort();
    times.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        if (minTime && t < minTime) opt.disabled = true;
        selectElement.appendChild(opt);
    });
}

async function computeLineForStep(step, ligneSelectElement) {
    const prevArrivee = steps[step - 1].arrivee ? steps[step - 1].arrivee.toLowerCase() : '';
    if (!prevArrivee) return;

    const usedLines = [];
    for (let i = 0; i < step; i++) {
        if (steps[i] && steps[i].ligne) {
            usedLines.push(steps[i].ligne);
        }
    }

    try {
        const response = await fetch('/api/reservation.php?findAllLignesFromCity=' + encodeURIComponent(prevArrivee));
        const data = await response.json();

        ligneSelectElement.innerHTML = '<option value="">Choisir une ligne</option>';

        const lineChecks = await Promise.all(data.map(async (ville) => {

            if (usedLines.includes(ville.LIG_NUM)) return null;

            const lineResponse = await fetch(`/api/reservation.php?ligne=${encodeURIComponent(ville.LIG_NUM)}`);
            const lineData = await lineResponse.json();

            const orderedVilles = [];
            lineData.forEach(item => {
                if (!orderedVilles.includes(item.VILLE_ARRET)) {
                    orderedVilles.push(item.VILLE_ARRET);
                }
            });

            const depIdx = orderedVilles.findIndex(v => v.toLowerCase() === prevArrivee);

            if (depIdx !== -1 && depIdx < orderedVilles.length - 1) {
                return ville.LIG_NUM;
            }

            return null;
        }));

        lineChecks.forEach(ligNum => {
            if (ligNum) {
                const option = document.createElement('option');
                option.value = ligNum;
                option.textContent = ligNum;
                ligneSelectElement.appendChild(option);
            }
        });

    } catch (error) {
        console.error("Erreur lors de la vérification des lignes disponibles :", error);
    }
}

let priceTimeout;
function triggerValidationAndPrice() {
    clearTimeout(priceTimeout);

    const allSteps = Object.values(steps);
    const isValid = allSteps.length > 0 && allSteps.every(s => s.ligne && s.depart && s.arrivee && s.departTime && s.arriveeTime);

    const btnConfirm = document.getElementById('btn-confirm');
    if (btnConfirm) btnConfirm.disabled = !isValid;

    if (!isValid) {
        document.getElementById('dynamic-price').textContent = '0.00 €';
        return;
    }

    priceTimeout = setTimeout(async () => {
        try {
            const formData = new FormData();
            formData.append('simulateTripPrice', JSON.stringify(allSteps));

            const response = await fetch('/api/reservation.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.prix !== undefined) {
                document.getElementById('dynamic-price').textContent = `${data.prix} €`;
            }
        } catch (e) {
            console.error("Erreur lors du calcul du prix :", e);
        }
    }, 500);
}

let originalFormHTML = '';

function confirm() {
    const lastStepIndex = Object.keys(steps).length - 1;
    const lastStep = steps[lastStepIndex];
    if (!lastStep.ligne || !lastStep.depart || !lastStep.arrivee || !lastStep.departTime || !lastStep.arriveeTime) {
        const lastRow = document.querySelector(`.search-form-horizontal[data-step="${lastStepIndex}"]`);
        lastRow.classList.add('errored');
        setTimeout(() => lastRow.classList.remove('errored'), 2000);
        return;
    }

    const busContainer = document.getElementById('bus-animation-container');
    if (busContainer) {
        const currentLeft = window.getComputedStyle(busContainer).left;
        let startRot = '0deg';
        if (busContainer.classList.contains('animate-bus-return')) {
            startRot = '180deg';
        }
        busContainer.style.setProperty('--start-left-forward', currentLeft);
        busContainer.style.setProperty('--start-rot-forward', startRot);

        busContainer.classList.remove('animate-bus', 'animate-bus-return');
        void busContainer.offsetWidth; // Force reflow
        busContainer.classList.add('animate-bus');
    }

    const stepsContainer = document.getElementById('steps');
    const searchActions = document.querySelector('.search-actions');

    stepsContainer.style.display = 'none';
    searchActions.style.display = 'none';

    const confContainer = document.createElement('div');
    confContainer.id = 'confirmation-view-container';
    stepsContainer.parentNode.insertBefore(confContainer, stepsContainer.nextSibling);

    const div = document.createElement('div');
    div.classList.add('confirmation-view');
    div.innerHTML = '<h3 style="color: #000; margin-bottom: 20px;">Récapitulatif de votre trajet</h3>';

    Object.keys(steps).forEach(step => {
        const stepData = steps[step];
        const stepDiv = document.createElement('div');
        stepDiv.classList.add('conf-step');
        const departTime = stepData.departTime || '-';
        const arriveeTime = stepData.arriveeTime || '-';
        stepDiv.innerHTML = `
            <div class="conf-step-header">Étape ${parseInt(step) + 1} : Ligne ${stepData.ligne}</div>
            <div class="conf-step-body">
                <span>De <strong>${stepData.depart}</strong> (${departTime})</span>
                <span class="arrow">→</span>
                <span>À <strong>${stepData.arrivee}</strong> (${arriveeTime})</span>
            </div>
        `;
        div.appendChild(stepDiv);
    });

    const actionsDiv = document.createElement('div');
    actionsDiv.style.display = 'flex';
    actionsDiv.style.gap = '15px';
    actionsDiv.style.marginTop = '20px';

    const payButton = document.createElement('button');
    payButton.type = 'button';
    payButton.classList.add('btn-pay');
    payButton.textContent = 'Confirmer et Payer';
    payButton.addEventListener('click', () => {
        const reservationData = new FormData();
        reservationData.append('setTripDetails', JSON.stringify(steps));


        fetch('/api/reservation.php?tripDepartureTime=' + (document.getElementById('date-depart').value ? new Date(document.getElementById('date-depart').value).toISOString() : ''), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(reservationData).toString()
        }).then(response => {
            if (response.ok) location.href = '/reservation/pay/';
        });
    });

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.classList.add('btn-cancel');
    cancelButton.textContent = 'Annuler';
    cancelButton.style.marginTop = '0';
    cancelButton.addEventListener('click', () => {
        confContainer.remove();
        stepsContainer.style.display = 'flex';
        searchActions.style.display = 'flex';

        const busContainer = document.getElementById('bus-animation-container');
        if (busContainer) {
            const currentLeft = window.getComputedStyle(busContainer).left;
            let startRot = '180deg';
            if (busContainer.classList.contains('animate-bus')) {
                startRot = '0deg';
            }
            busContainer.style.setProperty('--start-left-return', currentLeft);
            busContainer.style.setProperty('--start-rot-return', startRot);

            busContainer.classList.remove('animate-bus', 'animate-bus-return');
            void busContainer.offsetWidth; // Force reflow
            busContainer.classList.add('animate-bus-return');
        }
    });

    actionsDiv.appendChild(payButton);
    actionsDiv.appendChild(cancelButton);
    div.appendChild(actionsDiv);
    confContainer.appendChild(div);
}

function populateTimeSelect(selectElement, times, minTime) {
    if (!selectElement) return;
    const isDepart = selectElement.id.includes('depart');
    selectElement.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = isDepart ? 'Heure de départ' : 'Heure d\'arrivée';
    selectElement.appendChild(empty);
    times.sort();
    times.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        if (minTime && t < minTime) opt.disabled = true;
        selectElement.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    fetchLignes().then(() => addStep());

    document.getElementById('add-step-btn').addEventListener('click', () => {
        const lastStepIndex = Object.keys(steps).length - 1;
        const lastStep = steps[lastStepIndex];
        if (!lastStep.ligne || !lastStep.depart || !lastStep.arrivee || !lastStep.departTime || !lastStep.arriveeTime) {
            const lastRow = document.querySelector(`.search-form-horizontal[data-step="${lastStepIndex}"]`);
            lastRow.classList.add('errored');
            setTimeout(() => lastRow.classList.remove('errored'), 2000);
            return;
        }
        addStep();
    });
});

function removeSubsequentSteps(currentIndex) {
    const keys = Object.keys(steps).map(Number).sort((a, b) => a - b);
    let removed = false;

    keys.forEach(key => {
        if (key > currentIndex) {
            const wrapper = document.querySelector(`.step-wrapper[data-wrapper-step="${key}"]`);
            if (wrapper) wrapper.remove();

            delete steps[key];
            removed = true;
        }
    });

    if (removed) {
        const newKeys = Object.keys(steps).map(Number).sort((a, b) => a - b);
        if (newKeys.length > 1) {
            const lastKey = newKeys[newKeys.length - 1];
            const lastWrapper = document.querySelector(`.step-wrapper[data-wrapper-step="${lastKey}"]`);
            if (lastWrapper) {
                const lastBtn = lastWrapper.querySelector('.btn-remove-step');
                if (lastBtn) lastBtn.style.display = 'flex';
            }
        }
        triggerValidationAndPrice();
    }
}

function hideMap() {
    document.querySelector('.map-container').style.display = 'none';
}

function showMap() {
    document.querySelector('.map-container').style.display = 'block';
}