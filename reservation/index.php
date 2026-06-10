<?php
include '../includes/global.php';

// Récupération des villes
$villes = $database->listCities();
?>

<!-- Style spécifique à la page de réservation -->
<link rel="stylesheet" href="/assets/style/reservation.css">

<div class="reservation-hero">
    <div class="hero-bg-top"></div>
    <div class="hero-bg-bottom"></div>
    
    <div class="search-bar-container">
        <form action="index.php" method="GET" class="search-form-horizontal">
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
            
            <div class="input-group">
                <label for="date_voyage">Date du voyage</label>
                <input type="date" name="date_voyage" id="date_voyage" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <button type="submit" class="btn-search">Rechercher</button>
        </form>
    </div>
</div>

<datalist id="villes-list">
    <?php foreach ($villes as $ville) : ?>
        <option value="<?php echo htmlspecialchars($ville['COM_NOM']); ?>">
    <?php endforeach; ?>
</datalist>

<?php
// Affichage des résultats
if (isset($_GET['depart']) && isset($_GET['arrivee'])) {
    $depart = $_GET['depart'];
    $arrivee = $_GET['arrivee'];
    $date = $_GET['date_voyage'];
    
    echo '<section class="results-section">';
    echo '<h3>Trajets disponibles le ' . date('d/m/Y', strtotime($date)) . '</h3>';
    echo '<p style="color: var(--text-muted);">Aucun trajet trouvé entre <strong>' . htmlspecialchars($depart) . '</strong> et <strong>' . htmlspecialchars($arrivee) . '</strong> pour le moment.</p>';
    echo '</section>';
}
?>

<?php
require '../includes/footer.php';
?>