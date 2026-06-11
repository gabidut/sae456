<?php
include '../includes/global.php';

// Récupération des villes
$villes = $reservationManager->listCities();
$lignes = $ligneManager->getLignes();
?>

<!-- Style spécifique à la page de réservation -->
<link rel="stylesheet" href="/assets/style/reservation.css">
<script src="/assets/scripts/reservation.js" defer></script>
<div class="reservation-hero">
    <div class="hero-bg-top"></div>
    <div class="hero-bg-bottom"></div>

    <div class="search-bar-container" id="steps">
        <div class="search-form-horizontal">
            <button class="btn-search" onclick="confirm()">RESERVER</button>
        </div>
    </div>
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