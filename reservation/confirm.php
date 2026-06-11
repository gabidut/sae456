<?php
include '../includes/global.php';

$reservation = $session->getCurrentTripDetails();
$reservationManager->createReservation($reservation);
?>

<div class="reservation-hero" style="background-color: black !important; padding: 20px;">
    <h2>Merci <?= $session->isUserLoggedIn() ? $session->getUserSession()['CLI_PRENOM'] : '' ?> pour votre réservation !</h2>
    <?php
    foreach ($session->getCurrentTripDetails() as $key => $value) {
        echo '<p>' . htmlspecialchars($key) . ' : ' . htmlspecialchars($value['depart'])  . '(' . $value['departTime'] . ') -> ' . htmlspecialchars($value['arrivee']) . '(' . $value['arriveeTime'] . ') via ' . htmlspecialchars($value['ligne']) . '</p>';
    }
    ?>
</div>



<?php
require '../includes/footer.php';
?>