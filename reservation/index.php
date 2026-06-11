<?php
include '../includes/global.php';

// Récupération des villes
$villes = $reservationManager->listCities();
$lignes = $ligneManager->getLignes();
?>

<!-- Style spécifique à la page de réservation -->
<link rel="stylesheet" href="/assets/style/reservation.css?v=<?php echo time(); ?>">
<script src="/assets/scripts/reservation.js?v=<?php echo time(); ?>" defer></script>
<div class="reservation-hero">
    <div class="hero-bg-top"></div>
    <div class="hero-bg-bottom"></div>

    <div class="search-bar-container">
        <div class="search-main-layout">
            <div id="steps" class="steps-list">
            </div>
            <div class="search-actions">
                <button class="btn-search btn-reserve" onclick="showMap()">VOIR LA CARTE</button>
                <button class="btn-search btn-reserve" onclick="confirm()">RESERVER</button>
                <button class="btn-plus" id="add-step-btn" type="button" title="Ajouter une étape">+</button>
            </div>
        </div>
    </div>
</div>

<div class="map-container" style="z-index: 60; position: fixed; bottom: 30px; left: 5px; right: 0; height: 300px; width: 300px; border-radius: 20px; display: none;">
    <div style="width: 100%; border-radius: 5px 5px 0 0; background-color: white; display:flex; justify-content: end; align-items: center; cursor: pointer;" onclick="document.querySelector('.map-container').style.display = 'none'">
        <span style="color: black; padding-right: 15px;font-weight: 700;" onclick="hideMap()">X</span>
    </div>
    <iframe src="/reseau?BYPASS_DECO" frameborder="0" style="width: 100%; height: 100%;"></iframe>
</div>


<script>
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