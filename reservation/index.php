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

    <div class="search-bar-container">
        <div class="search-main-layout">
            <div id="steps" class="steps-list">
                <!-- Les étapes seront injectées ici par reservation.js -->
            </div>
            <div class="search-actions">
                <button class="btn-search btn-reserve" onclick="confirm()">RESERVER</button>
                <button class="btn-plus" id="add-step-btn" type="button" title="Ajouter une étape">+</button>
            </div>
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