<?php
include '../includes/global.php';

$reservation = $session->getCurrentTripDetails();
try {
    $resr = $reservationManager->createReservation($reservation);
} catch (Exception $e) {
    echo "Erreur lors de la création de la réservation : " . $e->getMessage();
    exit;
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="/assets/scripts/reservation-confirm.js" defer></script>

<div class="reservation-hero" style="background-color: black !important; padding: 20px;">
    <h2>Merci <?= $session->isUserLoggedIn() ? $session->getUserSession()['CLI_PRENOM'] : '' ?> pour votre réservation !</h2>
    <p>Votre numéro de réservation est : <?= $resr['cliNum'] ?>/<?= $resr['reservation_id'] ?></p>
    <p>Départ : <?= $resr['etapes'][0]['heure'] ?></p>
    <p>Prix total : <?= $resr['prix'] ?>€</p>
    <p>Points gagnés : <?= $resr['points'] ?></p>
    <div id="qrcode" style="padding: 20px; background-color: white;"></div>
    <button id="download-billet">Télécharger le billet</button>
</div>

<span id="reservation" style="display: none;">
    <?= json_encode($resr) ?>
</span>


<?php
require '../includes/footer.php';
?>