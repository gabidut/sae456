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

<style>
    /* Styles pour la structure latérale + carte */
    .map-layout-container {
        display: flex;
        width: 100%;
        background-color: #ececec;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        overflow: hidden;
        /* Empêche les débordements */
    }

    #sidebar {
        width: 350px;
        min-width: 350px;
        padding: 20px;
        background-color: #ffffff;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 15px;
        overflow-y: auto;
        z-index: 1000;
    }

    #map {
        flex-grow: 1;
        /* La carte prend tout l'espace restant */
        height: 100%;
        z-index: 1;
        /* Reste sous le panneau latéral en cas de chevauchement */
    }

    /* Styles des éléments du formulaire */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .form-group label {
        font-weight: bold;
        color: #333;
        font-size: 0.9em;
    }

    .form-group input {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1em;
    }

    /* Boutons de navigation du haut */
    .search-bar-container {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .search-bar-container button {
        flex: 1;
        padding: 10px;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.85em;
    }

    .search-bar-container button:hover {
        background-color: #0056b3;
    }
</style>

<main>
    <div class="map-layout-container" id="main-layout">

        <div id="sidebar">
            <div class="search-bar-container">
                <button onclick="location.href = '/reservation/auto'">Créateur de trajets</button>
                <button onclick="location.href = '/reservation/'" style="background-color: #6c757d;">Sélecteur d'étapes</button>
            </div>

            <p style="margin: 0; color: #666; font-size: 0.9em; font-style: italic;">
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

            <div id="steps" style="display: flex; flex-direction: column; gap: 15px; margin-top: 10px;"></div>
        </div>

        <div id="map"></div>

    </div>

    <?php require '../../includes/footer.php'; ?>
</main>

<?php if (isset($_GET['BYPASS_DECO'])) { ?>
    <style>
        #main-layout {
            height: 100vh;
        }
    </style>
<?php } else { ?>
    <style>
        #main-layout {
            height: 70vh;
        }
    </style>
<?php } ?>