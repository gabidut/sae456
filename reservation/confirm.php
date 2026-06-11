<?php
include '../includes/global.php';

$reservation = $session->getCurrentTripDetails();
var_dump($reservation);
$reservationManager->createReservation($reservation);
?>

<div class="reservation-hero" style="background-color: black !important; padding: 20px;">
    <h2>Merci <?= $session->isUserLoggedIn() ? $session->getUserSession()['CLI_PRENOM'] : '' ?> pour votre réservation !</h2>
</div>



<?php
require '../includes/footer.php';
?>