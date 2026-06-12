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
<link rel="stylesheet" href="/assets/style/reservation-auto.css">

<script src="/assets/scripts/map-selector.js" defer></script>
<script>
    window.userPoints = <?= $session->isUserLoggedIn() ? (int)$session->getUserSession()['CLI_NB_POINTS_EC'] : 0 ?>;
</script>

<main>
    <div class="map-layout-container <?php echo isset($_GET['BYPASS_DECO']) ? 'bypass-deco' : ''; ?>" id="main-layout">

        <div id="sidebar">
            <div class="navigation-pill-container">
                <button class="nav-pill-btn active" onclick="location.href = '/reservation/auto'">Créateur de trajets</button>
                <button class="nav-pill-btn" onclick="location.href = '/reservation/'">Sélecteur d'étapes</button>
            </div>

            <p class="sidebar-hint">
                Recherchez la ville de départ ou cliquez le point de départ sur la carte.
            </p>

            <div class="form-group">
                <label for="trip-date">Date et heure de départ :</label>
                <input type="datetime-local" id="trip-date" name="trip-date">
            </div>

            <div class="form-group">
                <label for="ville1">Départ :</label>
                <input type="text" list="cities" id="ville1" placeholder="Ex: Paris">
            </div>

            <div class="form-group">
                <label for="ville2">Arrivée :</label>
                <input type="text" list="cities" id="ville2" placeholder="Ex: Lyon">
            </div>

            <div id="steps"></div>
        </div>

        <div id="map"></div>

    </div>

    <?php require '../../includes/footer.php'; ?>
</main>