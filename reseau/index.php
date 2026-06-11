<?php


require_once '../includes/global.php';

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
<script src="/assets/scripts/map.js" defer></script>
<main>
    <div id="map"></div>


    <?php
    require '../includes/footer.php';
    ?>

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