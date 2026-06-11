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
    const stepIndex = Object.keys(steps).length;
    steps[stepIndex] = {};

    const lignesDatalist = document.createElement('datalist');
    lignesDatalist.id = 'lignes-list-' + stepIndex;
    document.body.appendChild(lignesDatalist);

    const dataList = document.createElement('datalist');
    dataList.id = `villes-list-${stepIndex}`;
    document.body.appendChild(dataList);

    // DIV BASE

    const div = document.createElement('div');
    div.classList.add('search-form-horizontal');

    // -- LINE GROUP --
    const ligneGroup = document.createElement('div');
    ligneGroup.classList.add('input-group');

    const ligneLabel = document.createElement('label');
    ligneLabel.setAttribute('for', 'ligne');
    ligneLabel.textContent = 'Ligne';

    const ligneInput = document.createElement('input');
    ligneInput.setAttribute('list', 'lignes-list-' + stepIndex);
    ligneInput.setAttribute('name', 'ligne');
    ligneInput.setAttribute('placeholder', 'Sélectionnez une ligne');
    ligneInput.setAttribute('required', '');
    ligneInput.setAttribute('autocomplete', 'off');


    // -- DEPART GROUP --
    const departGroup = document.createElement('div');
    departGroup.classList.add('input-group');

    const departLabel = document.createElement('label');
    departLabel.setAttribute('for', 'depart');
    departLabel.textContent = 'Départ';

    const departInput = document.createElement('input');
    departInput.setAttribute('list', `villes-list-${stepIndex}`);
    departInput.setAttribute('name', 'depart');
    departInput.setAttribute('placeholder', 'D\'où partez-vous ?');
    departInput.setAttribute('required', '');
    departInput.setAttribute('autocomplete', 'off');
    departInput.setAttribute('id', `depart-input-${stepIndex}`);

    // -- DEPART TIME SELECT --
    const departTimeSelect = document.createElement('select');
    departTimeSelect.setAttribute('id', `depart-time-${stepIndex}`);
    departTimeSelect.setAttribute('name', 'depart_time');
    departTimeSelect.innerHTML = '<option value="">Heure départ</option>';

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

    // -- ARRIVEE GROUP --
    const arriveeGroup = document.createElement('div');
    arriveeGroup.classList.add('input-group');

    const arriveeLabel = document.createElement('label');
    arriveeLabel.setAttribute('for', 'arrivee');
    arriveeLabel.textContent = 'Arrivée';

    const arriveeInput = document.createElement('input');
    arriveeInput.setAttribute('list', `villes-list-${stepIndex}`);
    arriveeInput.setAttribute('name', 'arrivee');
    arriveeInput.setAttribute('placeholder', 'Où allez-vous ?');
    arriveeInput.setAttribute('required', '');
    arriveeInput.setAttribute('autocomplete', 'off');

    // -- ARRIVEE TIME SELECT --
    const arriveeTimeSelect = document.createElement('select');
    arriveeTimeSelect.setAttribute('id', `arrivee-time-${stepIndex}`);
    arriveeTimeSelect.setAttribute('name', 'arrivee_time');
    arriveeTimeSelect.innerHTML = '<option value="">Heure arrivée</option>';

    // -- APPEND GROUPS --
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
    div.appendChild(buildDivider());

    const plusButton = document.createElement('button');
    plusButton.type = 'button';
    plusButton.classList.add('btn-search');
    plusButton.textContent = '+';
    plusButton.addEventListener('click', () => {
        if (!steps[stepIndex].ligne || !steps[stepIndex].depart || !steps[stepIndex].arrivee || !steps[stepIndex].departTime || !steps[stepIndex].arriveeTime) {
            div.classList.add('errored');
            setTimeout(() => {
                div.classList.remove('errored');
            }, 2000);
            return;
        } else {
            addStep();
        }
    });

    div.appendChild(plusButton);

    document.getElementById('steps').appendChild(div);


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
                })
                .catch(error => console.error('Erreur:', error));
        }
    });

    arriveeInput.addEventListener('change', function () {
        const arriveeValue = this.value;
        steps[stepIndex].arrivee = arriveeValue;
        console.log("Changing arrive : " + arriveeValue);

        const map = scheduleCache[stepIndex] || {};
        const minForArrive = steps[stepIndex].departTime || null;
        if (map[arriveeValue]) {
            populateTimeSelect(arriveeTimeSelect, map[arriveeValue], minForArrive);
        } else {
            arriveeTimeSelect.innerHTML = '<option value="">Heure arrivée</option>';
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
            departTimeSelect.innerHTML = '<option value="">Heure départ</option>';
        }
        steps[stepIndex].departTime = '';
    });

    departTimeSelect.addEventListener('change', function() {
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

    arriveeTimeSelect.addEventListener('change', function() {
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

    fetch('/api/reservation.php?findAllLignesFromCity=' + steps[step - 1].arrivee.toLowerCase() || '')
        .then(response => response.json())
        .then(data => {
            let stepsDatalist = document.getElementById(`lignes-list-${step}`);
            stepsDatalist.innerHTML = '';
            const lineNumber = steps[step - 1].ligne ? steps[step - 1].ligne.slice(0, -1) : null;

            data.forEach(ville => {
                if (lineNumber && ville.LIG_NUM === lineNumber) return;
                const option = document.createElement('option');
                option.value = ville.LIG_NUM;
                stepsDatalist.appendChild(option);
            });
        })
        .catch(error => console.error('Erreur:', error));
}

function confirm() {
    document.getElementById('steps').innerHTML = '';
    const div = document.createElement('div');
    div.classList.add('search-form-horizontal');

    div.innerHTML = '<h5>Confirmation : </h5>';
    Object.keys(steps).forEach(step => {
        const stepData = steps[step];
        const stepDiv = document.createElement('div');
        stepDiv.classList.add('input-group');
        const departTime = stepData.departTime || '-';
        const arriveeTime = stepData.arriveeTime || '-';
        stepDiv.innerHTML = `<strong>Etape ${parseInt(step) + 1} :</strong> Ligne ${stepData.ligne}, de ${stepData.depart} (${departTime}) à ${stepData.arrivee} (${arriveeTime})`;
        div.appendChild(stepDiv);
    });

    const payButton = document.createElement('button');
    payButton.type = 'button';
    payButton.classList.add('btn-search');
    payButton.textContent = 'Payer';

    div.appendChild(payButton);

    payButton.addEventListener('click', () => {
        const reservationData = new FormData();
        reservationData.append('setTripDetails', JSON.stringify(steps));
        fetch('/api/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(reservationData).toString()
        }).then(response => {
            if (response.ok) {
                location.href = '/reservation/pay/';
            }
        })
    });
    document.getElementById('steps').appendChild(div);
}

(() => {
    fetchLignes().then(() => {
        addStep();
    });
})();

function populateTimeSelect(selectElement, times, minTime) {
    if (!selectElement) return;
    selectElement.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Choisir l\'heure';
    selectElement.appendChild(empty);
    times.sort();
    times.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        if (minTime && t < minTime) {
            opt.disabled = true;
        }
        selectElement.appendChild(opt);
    });
}