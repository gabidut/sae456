<?php


require_once '../includes/global.php';

$lignes = $ligneManager->getLignes();

$directions = [];

$horaires = [];

$grille = [];

if(isset($_GET['ligne']))
{
    $directions = $ligneManager->getDirections($_GET['ligne']);
}

if(isset($_GET['direction']))
{
    $horaires = $ligneManager->getHoraire($_GET['direction']);

    foreach($horaires as $h)
    {
        $ville = $h['VILLE_ARRET'];
        $heure = $h['HEURE_PASSAGE'];

        $grille[$ville][] = $heure;
    }
}

?>


<?php
require '../includes/footer.php'; 
?>