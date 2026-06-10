<?php
include '../includes/global.php';

// Récupération des villes
$villes = $reservationManager->listCities();
$lignes = $ligneManager->getLignes();
?>

<!-- Style spécifique à la page de réservation -->
<link rel="stylesheet" href="/assets/style/reservation.css">

<div class="reservation-hero">
    <div class="hero-bg-top"></div>
    <div class="hero-bg-bottom"></div>
    
    <div class="search-bar-container">
        <form action="index.php" method="GET" class="search-form-horizontal">

            <div class="input-group">
                <label for="ligne">Ligne</label>
                <input list="lignes-list" name="ligne" id="ligne" placeholder="Sélectionnez une ligne" required autocomplete="off">
            </div>
            
            <div class="divider"></div>
            <div class="input-group">
                <label for="depart">Départ</label>
                <input list="villes-list" name="depart" id="depart" placeholder="D'où partez-vous ?" required autocomplete="off">
            </div>
            
            <div class="divider"></div>
            
            <div class="input-group">
                <label for="arrivee">Arrivée</label>
                <input list="villes-list" name="arrivee" id="arrivee" placeholder="Où allez-vous ?" required autocomplete="off">
            </div>
            
            <div class="divider"></div>
            
            <button type="submit" class="btn-search">Rechercher</button>
        </form>
    </div>
</div>

<datalist id="villes-list">
    <?php foreach ($villes as $ville) : ?>
        <option value="<?php echo htmlspecialchars($ville['COM_NOM']); ?>"/>
    <?php endforeach; ?>
</datalist>

<datalist id="lignes-list">
    <?php foreach ($lignes as $ligne) : ?>
        <option value="<?php echo htmlspecialchars($ligne['LIG_NUM']); ?>"/>
    <?php endforeach; ?>
</datalist>

<script>
document.getElementById('ligne').addEventListener('change', function() {
    const ligneValue = this.value;
    
    if (ligneValue) {
        fetch(`/api/reservation.php?ligne=${encodeURIComponent(ligneValue)}`)
            .then(response => response.json())
            .then(data => {
                // Récupérer les villes uniques
                const villes = [...new Set(data.map(step => step.VILLE_ARRET))];
                
                let stepsDatalist = document.getElementById('etapes-list');
                if (!stepsDatalist) {
                    stepsDatalist = document.createElement('datalist');
                    stepsDatalist.id = 'etapes-list';
                    document.body.appendChild(stepsDatalist);
                } else {
                    stepsDatalist.innerHTML = '';
                }
                
                // Remplir la datalist avec les villes uniques
                villes.forEach(ville => {
                    const option = document.createElement('option');
                    option.value = ville;
                    stepsDatalist.appendChild(option);
                });
                
                // Mettre à jour les listes de départ et arrivée
                document.getElementById('depart').setAttribute('list', 'etapes-list');
                document.getElementById('arrivee').setAttribute('list', 'etapes-list');
                
                // Vider les valeurs précédentes
                document.getElementById('depart').value = '';
                document.getElementById('arrivee').value = '';
            })
            .catch(error => console.error('Erreur:', error));
    }
});
</script>
<?php
if (isset($_GET['depart']) && isset($_GET['arrivee'])) {
    $depart = $_GET['depart'];
    $arrivee = $_GET['arrivee'];
    
    echo '<section class="results-section">';
    echo '<p style="color: var(--text-muted);">Aucun trajet trouvé entre <strong>' . htmlspecialchars($depart) . '</strong> et <strong>' . htmlspecialchars($arrivee) . '</strong> pour le moment.</p>';
    echo '</section>';
}
?>

<?php
require '../includes/footer.php';
?>