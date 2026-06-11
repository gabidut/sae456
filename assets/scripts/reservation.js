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

    // 1. DEUX datalists séparées pour Départ et Arrivée
    const departDataList = document.createElement('datalist');
    departDataList.id = `depart-list-${stepIndex}`;
    document.body.appendChild(departDataList);

    const arriveeDataList = document.createElement('datalist');
    arriveeDataList.id = `arrivee-list-${stepIndex}`;
    document.body.appendChild(arriveeDataList);

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
    departInput.setAttribute('list', `depart-list-${stepIndex}`);
    departInput.setAttribute('placeholder', 'D\'où partez-vous ?');
    departInput.setAttribute('required', '');
    departInput.setAttribute('autocomplete', 'off');
    departInput.setAttribute('id', `depart-input-${stepIndex}`);
    departInput.disabled = true;

    const departTimeSelect = document.createElement('select');
    departTimeSelect.setAttribute('id', `depart-time-${stepIndex}`);
    departTimeSelect.innerHTML = '<option value="">Heure de départ</option>';
    departTimeSelect.disabled = true;

    // -- ARRIVEE GROUP --
    const arriveeGroup = document.createElement('div');
    arriveeGroup.classList.add('input-group');

    const arriveeLabel = document.createElement('label');
    arriveeLabel.textContent = 'Arrivée';

    const arriveeInput = document.createElement('input');
    arriveeInput.setAttribute('list', `arrivee-list-${stepIndex}`);
    arriveeInput.setAttribute('placeholder', 'Où allez-vous ?');
    arriveeInput.setAttribute('required', '');
    arriveeInput.setAttribute('autocomplete', 'off');
    arriveeInput.disabled = true;

    const arriveeTimeSelect = document.createElement('select');
    arriveeTimeSelect.setAttribute('id', `arrivee-time-${stepIndex}`);
    arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
    arriveeTimeSelect.disabled = true; // Toujours désactivé
    arriveeTimeSelect.style.appearance = 'none'; // Rend l'aspect plus "lecture seule"

    if (stepIndex > 0) {
        departInput.value = steps[stepIndex - 1].arrivee || '';
        departInput.setAttribute('readonly', '');
        departInput.disabled = false; // Désactivé = grisé, Readonly = bloqué mais actif visuellement
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
    ligneInput.focus();

    // ==========================================
    // ÉVÉNEMENTS
    // ==========================================

    ligneInput.addEventListener('change', function () {
        const ligneValue = this.value;
        steps[stepIndex].ligne = ligneValue;
        
        // Réinitialisation de la suite logique
        departTimeSelect.innerHTML = '<option value="">Heure de départ</option>';
        departTimeSelect.disabled = true;
        steps[stepIndex].departTime = '';
        
        arriveeInput.value = '';
        arriveeInput.disabled = true;
        steps[stepIndex].arrivee = '';
        
        arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
        steps[stepIndex].arriveeTime = '';
        
        if (ligneValue) {
            fetch(`/api/reservation.php?ligne=${encodeURIComponent(ligneValue)}`)
                .then(response => response.json())
                .then(data => {
                    const map = {};
                    const orderedVilles = []; // 2. On garde l'ordre des arrêts

                    data.forEach(item => {
                        const ville = item.VILLE_ARRET;
                        const time = item.HEURE_PASSAGE;
                        if (!map[ville]) {
                            map[ville] = [];
                            orderedVilles.push(ville); // L'ordre de l'API est préservé
                        }
                        if (time && !map[ville].includes(time)) map[ville].push(time);
                    });

                    // 3. On sauvegarde la carte ET l'ordre dans le cache
                    scheduleCache[stepIndex] = { map: map, orderedVilles: orderedVilles };

                    // Remplir les suggestions de départ avec TOUTES les villes
                    departDataList.innerHTML = '';
                    arriveeDataList.innerHTML = '';
                    orderedVilles.forEach(ville => {
                        const opt = document.createElement('option');
                        opt.value = ville;
                        departDataList.appendChild(opt);
                        arriveeDataList.appendChild(opt.cloneNode(true)); // Par défaut, on met tout
                    });
                    
                    // Si une ville de départ était déjà saisie, on vérifie si elle est sur la ligne
                    if (departInput.value && !orderedVilles.includes(departInput.value)) {
                        departInput.value = '';
                        steps[stepIndex].depart = '';
                    }

                    if (stepIndex === 0) {
                        departInput.disabled = false;
                        
                        // Si le départ est valide, on déclenche l'événement change pour filtrer la suite
                        if (departInput.value && map[departInput.value]) {
                            departInput.dispatchEvent(new Event('change'));
                        }
                    } else {
                        // Pour l'étape > 0, le départ est déjà défini, on peut directement activer l'heure de départ
                        const departVal = departInput.value;
                        const prevArrTime = steps[stepIndex - 1].arriveeTime || null;
                        if (departVal && map[departVal]) {
                            populateTimeSelect(departTimeSelect, map[departVal], prevArrTime);
                            departTimeSelect.disabled = false;
                        }
                    }
                });
        } else {
            // Si la ligne est vidée mais qu'on a déjà un départ, on repeuple la liste globale
            if (typeof globalVilles !== 'undefined') {
                departDataList.innerHTML = '';
                globalVilles.forEach(ville => {
                    const opt = document.createElement('option');
                    opt.value = ville.COM_NOM;
                    departDataList.appendChild(opt);
                });
            }
        }
    });

    departInput.addEventListener('change', function () {
        const departValue = this.value;
        steps[stepIndex].depart = departValue;

        const cache = scheduleCache[stepIndex] || {};
        const map = cache.map || {};
        const orderedVilles = cache.orderedVilles || [];

        // 4. Filtrer la liste d'arrivée en fonction du départ
        arriveeDataList.innerHTML = '';
        const departIndex = orderedVilles.indexOf(departValue);

        if (departIndex !== -1) {
            // N'ajoute que les villes APRES l'index de départ
            for (let i = departIndex + 1; i < orderedVilles.length; i++) {
                const option = document.createElement('option');
                option.value = orderedVilles[i];
                arriveeDataList.appendChild(option);
            }
        }

        // Réinitialisation de la suite
        departTimeSelect.innerHTML = '<option value="">Heure de départ</option>';
        departTimeSelect.disabled = true;
        steps[stepIndex].departTime = '';
        
        arriveeInput.value = '';
        arriveeInput.disabled = true;
        steps[stepIndex].arrivee = '';
        
        arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
        steps[stepIndex].arriveeTime = '';

        const prevArrTime2 = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
        if (map[departValue]) {
            populateTimeSelect(departTimeSelect, map[departValue], prevArrTime2);
            departTimeSelect.disabled = false;
        }
    });
    
    departTimeSelect.addEventListener('change', function() {
        const selected = this.value;
        const prevArr = stepIndex > 0 ? (steps[stepIndex - 1].arriveeTime || null) : null;
        if (prevArr && selected && selected < prevArr) {
            alert('L\'heure de départ doit être après l\'heure d\'arrivée précédente.');
            this.value = '';
            steps[stepIndex].departTime = '';
            arriveeInput.value = '';
            arriveeInput.disabled = true;
            arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
            steps[stepIndex].arriveeTime = '';
            return;
        }
        
        steps[stepIndex].departTime = selected;
        
        if (selected) {
            arriveeInput.disabled = false;
            
            // Si une arrivée est déjà sélectionnée, recalculer l'heure d'arrivée
            const arriveeVal = arriveeInput.value;
            if (arriveeVal) {
                updateArriveeTime(stepIndex, arriveeVal, selected);
            }
        } else {
            arriveeInput.value = '';
            arriveeInput.disabled = true;
            arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
            steps[stepIndex].arriveeTime = '';
        }
    });

    arriveeInput.addEventListener('change', function () {
        const arriveeValue = this.value;
        steps[stepIndex].arrivee = arriveeValue;
        const cache = scheduleCache[stepIndex] || {};
        const orderedVilles = cache.orderedVilles || [];

        // 6. Sécurité si l'utilisateur force la saisie clavier d'un arrêt précédent
        if (departInput.value) {
            const dIndex = orderedVilles.indexOf(departInput.value);
            const aIndex = orderedVilles.indexOf(arriveeValue);

            if (aIndex !== -1 && dIndex !== -1 && aIndex <= dIndex) {
                alert("La destination doit se trouver après le point de départ de la ligne.");
                this.value = '';
                steps[stepIndex].arrivee = '';
                arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
                steps[stepIndex].arriveeTime = '';
                return;
            }
        }
        
        const departTime = steps[stepIndex].departTime;
        if (arriveeValue && departTime) {
             updateArriveeTime(stepIndex, arriveeValue, departTime);
        } else {
             arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
             steps[stepIndex].arriveeTime = '';
        }

        if (stepIndex < Object.keys(steps).length - 1) {
            const nextDepartInput = document.getElementById(`depart-input-${stepIndex + 1}`);
            if (nextDepartInput) {
                nextDepartInput.value = arriveeValue;
                steps[stepIndex + 1].depart = arriveeValue;
                computeLineForStep(stepIndex + 1);
            }
        }
    });
}

function updateArriveeTime(stepIndex, arriveeValue, minTime) {
    const cache = scheduleCache[stepIndex] || {};
    const map = cache.map || {};
    const arriveeTimeSelect = document.getElementById(`arrivee-time-${stepIndex}`);
    
    if (map[arriveeValue] && minTime) {
        // On prend le premier horaire d'arrivée disponible après l'heure de départ
        const times = map[arriveeValue].filter(t => t > minTime);
        if (times.length > 0) {
            times.sort();
            steps[stepIndex].arriveeTime = times[0];
            arriveeTimeSelect.innerHTML = `<option value="${times[0]}">${times[0]}</option>`;
        } else {
            arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
            steps[stepIndex].arriveeTime = '';
            alert("Aucun horaire d\'arrivée trouvé pour cette destination après le départ.");
        }
    } else {
        arriveeTimeSelect.innerHTML = '<option value="">Heure d\'arrivée</option>';
        steps[stepIndex].arriveeTime = '';
    }
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
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.LIG_NUM;
                stepsDatalist.appendChild(option);
            });
        });
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

    // Lancer l'animation du bus au moment de la réservation
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

    // Sauvegarder l'état actuel pour pouvoir annuler
    const stepsContainer = document.getElementById('steps');
    const searchActions = document.querySelector('.search-actions');

    // Masquer le formulaire plutôt que de le vider
    stepsContainer.style.display = 'none';
    searchActions.style.display = 'none';

    // Créer un conteneur pour la vue confirmation
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

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.classList.add('btn-cancel');
    cancelButton.textContent = 'Annuler';
    cancelButton.style.marginTop = '0';
    cancelButton.addEventListener('click', () => {
        // Supprimer la vue confirmation
        confContainer.remove();
        // Réafficher le formulaire original (les données sont préservées dans l'objet 'steps' et dans le DOM)
        stepsContainer.style.display = 'flex';
        searchActions.style.display = 'flex';

        // Lancer l'animation de retour du bus (U-Turn)
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

function hideMap() {
    document.querySelector('.map-container').style.display = 'none';
}

function showMap() {
    document.querySelector('.map-container').style.display = 'block';
}