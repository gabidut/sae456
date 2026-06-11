let steps = {};
let lignes = [];
let scheduleCache = {};

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
    // Masquer les boutons de suppression des étapes précédentes
    document.querySelectorAll('.btn-remove-step').forEach(btn => btn.style.display = 'none');

    const stepIndex = Object.keys(steps).length;
    steps[stepIndex] = {};

    const lignesDatalist = document.createElement('datalist');
    lignesDatalist.id = 'lignes-list-' + stepIndex;
    document.body.appendChild(lignesDatalist);

    const dataList = document.createElement('datalist');
    dataList.id = `villes-list-${stepIndex}`;
    document.body.appendChild(dataList);

    const div = document.createElement('div');
    div.classList.add('search-form-horizontal');
    div.dataset.step = stepIndex;

    // -- LINE GROUP --
    const ligneGroup = document.createElement('div');
    ligneGroup.classList.add('input-group');

    const ligneLabel = document.createElement('label');
    ligneLabel.textContent = 'Ligne';

    const ligneInput = document.createElement('input');
    ligneInput.setAttribute('list', 'lignes-list-' + stepIndex);
    ligneInput.setAttribute('placeholder', 'Ligne');
    ligneInput.setAttribute('required', '');
    ligneInput.setAttribute('autocomplete', 'off');

    // -- DEPART GROUP --
    const departGroup = document.createElement('div');
    departGroup.classList.add('input-group');

    const departLabel = document.createElement('label');
    departLabel.textContent = 'Départ';

    const departInput = document.createElement('input');
    departInput.setAttribute('list', `villes-list-${stepIndex}`);
    departInput.setAttribute('placeholder', 'D\'où partez-vous ?');
    departInput.setAttribute('required', '');
    departInput.setAttribute('autocomplete', 'off');
    departInput.setAttribute('id', `depart-input-${stepIndex}`);

    const departTimeSelect = document.createElement('select');
    departTimeSelect.setAttribute('id', `depart-time-${stepIndex}`);
    departTimeSelect.innerHTML = '<option value="">Heure</option>';

    // -- ARRIVEE GROUP --
    const arriveeGroup = document.createElement('div');
    arriveeGroup.classList.add('input-group');

    const arriveeLabel = document.createElement('label');
    arriveeLabel.textContent = 'Arrivée';

    const arriveeInput = document.createElement('input');
    arriveeInput.setAttribute('list', `villes-list-${stepIndex}`);
    arriveeInput.setAttribute('placeholder', 'Où allez-vous ?');
    arriveeInput.setAttribute('required', '');
    arriveeInput.setAttribute('autocomplete', 'off');

    const arriveeTimeSelect = document.createElement('select');
    arriveeTimeSelect.setAttribute('id', `arrivee-time-${stepIndex}`);
    arriveeTimeSelect.innerHTML = '<option value="">Heure</option>';

    if (stepIndex > 0) {
        departInput.value = steps[stepIndex - 1].arrivee || '';
        departInput.setAttribute('readonly', '');
        steps[stepIndex].depart = departInput.value;
        computeLineForStep(stepIndex);
    } else {
        lignes.forEach(ligne => {
            const optionA = document.createElement('option');
            optionA.value = ligne.LIG_NUM;
            lignesDatalist.appendChild(optionA);
        });
    }

    ligneGroup.appendChild(ligneLabel);
    ligneGroup.appendChild(ligneInput);

    departGroup.appendChild(departLabel);
    departGroup.appendChild(departInput);
    departGroup.appendChild(departTimeSelect);

    arriveeGroup.appendChild(arriveeLabel);
    arriveeGroup.appendChild(arriveeInput);
    arriveeGroup.appendChild(arriveeTimeSelect);

    div.appendChild(ligneGroup);
    div.appendChild(buildDivider());
    div.appendChild(departGroup);
    div.appendChild(buildDivider());
    div.appendChild(arriveeGroup);

    const stepWrapper = document.createElement('div');
    stepWrapper.classList.add('step-wrapper');
    stepWrapper.dataset.wrapperStep = stepIndex;
    stepWrapper.appendChild(div);

    // -- REMOVE BUTTON --
    if (stepIndex > 0) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.classList.add('btn-remove-step');
        removeBtn.innerHTML = '&times;';
        removeBtn.title = 'Supprimer cette étape';
        removeBtn.addEventListener('click', () => {
            stepWrapper.remove();
            delete steps[stepIndex];
            
            // Réafficher le bouton sur la nouvelle dernière étape
            const keys = Object.keys(steps).map(Number).sort((a,b) => a-b);
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

    // Event listeners
    ligneInput.addEventListener('change', function () {
        const ligneValue = this.value;
        steps[stepIndex].ligne = ligneValue;
        if (ligneValue) {
            fetch(`/api/reservation.php?ligne=${encodeURIComponent(ligneValue)}`)
                .then(response => response.json())
                .then(data => {
                    const map = {};
                    data.forEach(item => {
                        const ville = item.VILLE_ARRET;
                        const time = item.HEURE_PASSAGE;
                        if (!map[ville]) map[ville] = [];
                        if (time && !map[ville].includes(time)) map[ville].push(time);
                    });
                    scheduleCache[stepIndex] = map;
                    const villes = Object.keys(map);
                    let stepsDatalist = document.getElementById(`villes-list-${stepIndex}`);
                    stepsDatalist.innerHTML = '';
                    villes.forEach(ville => {
                        const option = document.createElement('option');
                        option.value = ville;
                        stepsDatalist.appendChild(option);
                    });
                    const departVal = departInput.value;
                    const prevArrTime = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
                    if (departVal && map[departVal]) populateTimeSelect(departTimeSelect, map[departVal], prevArrTime);
                    const arriveeVal = arriveeInput.value;
                    const departSelectedTime = steps[stepIndex].departTime || departTimeSelect.value || null;
                    if (arriveeVal && map[arriveeVal]) populateTimeSelect(arriveeTimeSelect, map[arriveeVal], departSelectedTime);
                });
        }
    });

    arriveeInput.addEventListener('change', function () {
        const arriveeValue = this.value;
        steps[stepIndex].arrivee = arriveeValue;
        const map = scheduleCache[stepIndex] || {};
        const minForArrive = steps[stepIndex].departTime || null;

        if(!departTimeSelect.value && minForArrive) {
            arriveeInput.value = '';
            steps[stepIndex].arrivee = '';
            return;
        }
        fetch(`/api/reservation.php?getFinalHoraire=1&lineId=${steps[stepIndex].ligne}&codeInseeDepart=${encodeURIComponent(steps[stepIndex].depart)}&codeInseeArrivee=${encodeURIComponent(arriveeValue)}&horaireDepart=${encodeURIComponent(minForArrive)}`)
            .then(response => response.json())
            .then(data => {
                console.log(data);

                if (!data || !data.horaires) {
                    arriveeTimeSelect.innerHTML = '<option value="">Aucun horaire disponible</option>';
                    return;
                }
                const times = data.horaires || [];
                arriveeTimeSelect.innerHTML = '';
                times.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    arriveeTimeSelect.appendChild(option);
                });
                if(times > 0) {
                    arriveeTimeSelect.value = times[1];
                    steps[stepIndex].arriveeTime = times[1];
                }
            }).catch(error => {
                console.error('Erreur lors de la récupération des horaires d\'arrivée :', error);
                arriveeTimeSelect.innerHTML = '<option value="">Aucun horaire disponible</option>';
            });
        if (map[arriveeValue]) {

        } else {
            arriveeTimeSelect.innerHTML = '<option value="">Aucun horaire disponible</option>';
        }
        steps[stepIndex].arriveeTime = '';
        if (stepIndex < Object.keys(steps).length - 1) {
            const nextDepartInput = document.getElementById(`depart-input-${stepIndex + 1}`);
            if (nextDepartInput) {
                nextDepartInput.value = arriveeValue;
                steps[stepIndex + 1].depart = arriveeValue;
                computeLineForStep(stepIndex + 1);
            }
        }
    });

    departInput.addEventListener('change', function () {
        const departValue = this.value;
        steps[stepIndex].depart = departValue;
        const map = scheduleCache[stepIndex] || {};
        const prevArrTime2 = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
        if (map[departValue]) {
            populateTimeSelect(departTimeSelect, map[departValue], prevArrTime2);
        } else {
            departTimeSelect.innerHTML = '<option value="">Heure</option>';
        }
        steps[stepIndex].departTime = '';
    });

    departTimeSelect.addEventListener('change', function () {
        const selected = this.value;
        const prevArr = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
        if (prevArr && selected && selected < prevArr) {
            alert('L\'heure de départ doit être après l\'heure d\'arrivée précédente.');
            this.value = '';
            steps[stepIndex].departTime = '';
            return;
        }
        steps[stepIndex].departTime = selected;
        const map = scheduleCache[stepIndex] || {};
        const arriveeValLocal = arriveeInput.value;
        if (arriveeValLocal && map[arriveeValLocal]) {
            populateTimeSelect(arriveeTimeSelect, map[arriveeValLocal], selected);
            if (steps[stepIndex].arriveeTime && steps[stepIndex].arriveeTime < selected) {
                steps[stepIndex].arriveeTime = '';
                arriveeTimeSelect.value = '';
            }
        }
    });

    arriveeTimeSelect.addEventListener('change', function () {
        const selected = this.value;
        const departSel = steps[stepIndex].departTime || null;
        if (departSel && selected && selected < departSel) {
            alert('L\'heure d\'arrivée doit être après l\'heure de départ.');
            this.value = '';
            steps[stepIndex].arriveeTime = '';
            return;
        }
        steps[stepIndex].arriveeTime = selected;
        const nextIndex = stepIndex + 1;
        const nextDepartSelect = document.getElementById(`depart-time-${nextIndex}`);
        if (nextDepartSelect) {
            const nextMap = scheduleCache[nextIndex] || {};
            const nextDepartStation = steps[nextIndex] && steps[nextIndex].depart ? steps[nextIndex].depart : null;
            if (nextMap && nextDepartStation && nextMap[nextDepartStation]) {
                populateTimeSelect(nextDepartSelect, nextMap[nextDepartStation], selected);
                if (steps[nextIndex] && steps[nextIndex].departTime && steps[nextIndex].departTime < selected) {
                    steps[nextIndex].departTime = '';
                    nextDepartSelect.value = '';
                }
            }
        }
    });
}

function buildDivider() {
    const divider = document.createElement('div');
    divider.classList.add('divider');
    return divider;
}

function computeLineForStep(step) {
    fetch('/api/reservation.php?findAllLignesFromCity=' + (steps[step - 1].arrivee ? steps[step - 1].arrivee.toLowerCase() : ''))
        .then(response => response.json())
        .then(data => {
            let stepsDatalist = document.getElementById(`lignes-list-${step}`);
            if (!stepsDatalist) return;
            stepsDatalist.innerHTML = '';
            const lineNumber = steps[step - 1].ligne ? steps[step - 1].ligne.slice(0, -1) : null;
            data.forEach(ville => {
                if (lineNumber && ville.LIG_NUM === lineNumber) return;
                const option = document.createElement('option');
                option.value = ville.LIG_NUM;
                stepsDatalist.appendChild(option);
            });
        });
}

function confirm() {
    const lastStepIndex = Object.keys(steps).length - 1;
    const lastStep = steps[lastStepIndex];
    if (!lastStep.ligne || !lastStep.depart || !lastStep.arrivee || !lastStep.departTime || !lastStep.arriveeTime) {
        const lastRow = document.querySelector(`.search-form-horizontal[data-step="${lastStepIndex}"]`);
        lastRow.classList.add('errored');
        setTimeout(() => lastRow.classList.remove('errored'), 2000);
        return;
    }

    document.getElementById('steps').innerHTML = '';
    document.querySelector('.search-actions').style.display = 'none';

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

    const payButton = document.createElement('button');
    payButton.type = 'button';
    payButton.classList.add('btn-search', 'btn-pay');
    payButton.textContent = 'Confirmer et Payer';
    payButton.addEventListener('click', () => {
        const reservationData = new FormData();
        reservationData.append('setTripDetails', JSON.stringify(steps));
        console.log(steps);

        fetch('/api/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(reservationData).toString()
        }).then(response => {
            if (response.ok) location.href = '/reservation/pay/';
        });
    });

    div.appendChild(payButton);
    document.getElementById('steps').appendChild(div);
}

function populateTimeSelect(selectElement, times, minTime) {
    if (!selectElement) return;
    selectElement.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Heure';
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

function hideMap() {
    document.querySelector('.map-container').style.display = 'none';
}

function showMap() {
    document.querySelector('.map-container').style.display = 'block';
}   