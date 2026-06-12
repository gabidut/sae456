<?php
include '../includes/global.php';

// Récupération des villes
$villes = $reservationManager->listCities();
$lignes = $ligneManager->getLignes();
?>

<!-- Style spécifique à la page de réservation -->
<link rel="stylesheet" href="/assets/style/reservation.css?v=<?php echo time(); ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js" type="text/javascript"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
<script src="/assets/scripts/reservation.js?v=<?php echo time(); ?>" defer></script>
<div class="reservation-hero">
    <div class="hero-bg-top"></div>
    <div class="hero-bg-bottom"></div>

    <div id="bus-animation-container" class="bus-container">
        <model-viewer src="/Bus.glb" autoplay animation-name="Squash" shadow-intensity="1" camera-orbit="0deg 90deg auto" interaction-prompt="none" loading="eager">
            <div slot="progress-bar" style="display: none;"></div>
        </model-viewer>
    </div>

    <div class="search-bars">
        <div class="nav-container">
            <button class="btn-nav" onclick="location.href = '/reservation/auto'">Créateur de trajets</button>
            <button class="btn-nav active" onclick="location.href = '/reservation/'">Sélecteur d'étapes</button>
        </div>

        <div class="search-bar-container">
            <label for="date-depart">Date du trajet : </label>
            <input type="date" id="date-depart" name="date-depart" min="<?= date('Y-m-d') ?>" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
            <div class="search-main-layout">
                <div id="steps" class="steps-list">
                </div>
                <div class="search-actions" style="display: flex; align-items: center; gap: 15px;">
                    <div class="price-display" style="font-size: 1.2rem; font-weight: bold; padding: 10px 20px; background: #f0f0f0; border-radius: 8px;">
                        Total estimé : <span id="dynamic-price">0.00 €</span>
                    </div>
                    <button class="btn-search btn-reserve" onclick="showMap()">VOIR LA CARTE</button>
                    <button class="btn-search btn-reserve" id="btn-confirm" onclick="confirm()" disabled>RESERVER</button>
                    <button class="btn-plus" id="add-step-btn" type="button" title="Ajouter une étape">+</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="map-container" style="z-index: 99999; position: fixed; bottom: 30px; left: 5px; height: 300px; width: 300px; display: none; overflow: hidden; background-color: white; border-radius: 20px 20px 0 0;">

    <div class="map-drag" style="height: 30px; width: 100%; background-color: #f8f9fa; display:flex; justify-content: end; align-items: center; cursor: move;">
        <span style="color: black; padding-right: 15px; font-weight: 700; cursor: pointer;" onclick="hideMap()">X</span>
    </div>

    <iframe src="/reseau?BYPASS_DECO" frameborder="0" style="width: 100%; height: calc(100% - 30px); display: block;"></iframe>
</div>


<script defer>
    $(function() {
        $(".map-container").draggable({
            handle: ".map-drag",
            iframeFix: true
        });

        $(".map-container").resizable({
            start: function(event, ui) {
                $(this).find("iframe").css("pointer-events", "none");
            },
            stop: function(event, ui) {
                $(this).find("iframe").css("pointer-events", "auto");
            }
        });
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