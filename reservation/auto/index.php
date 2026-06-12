<?php


require_once '../../includes/global.php';

$lignes = $ligneManager->getLignes();

$directions = [];

$horaires = [];

$grille = [];

if (isset($_GET['ligne'])) {
    $directions = $ligneManager->getDirections($_GET['ligne']);
}

if (isset($_GET['direction'])) {
    $horaires = $ligneManager->getHoraire($_GET['direction']);

    foreach ($horaires as $h) {
        $ville = $h['VILLE_ARRET'];
        $heure = $h['HEURE_PASSAGE'];

        $grille[$ville][] = $heure;
    }
}

?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="/assets/scripts/map-selector.js" defer></script>
<main>
            <div class="search-bar-container">
            <button onclick="location.href = '/reservation/auto'">Créateur de trajets</button>
            <button onclick="location.href = '/reservation/'">Sélecteur d'étapes</button>
        </div>
    <p>Recherchez la ville de départ ou cliquez le point de départ</p>
    <input type="datetime-local" id="trip-date" name="trip-date">
    <input type="text" list="cities" id="ville1" placeholder="Départ">
    <input type="text" list="cities" id="ville2" placeholder="Arrivée">

    <div id="map"></div>

    <div id="steps" style="display: flex; gap: 20px; margin-top: 20px;"></div>

    <?php require '../../includes/footer.php'; ?>
</main>

<?php if (isset($_GET['BYPASS_DECO'])) { ?>
    <style>
        #map {
            height: 100vh;
        }
    </style>
<?php } else { ?>
    <style>
        #map {
            height: 70vh;
        }
    </style>
<?php } ?>