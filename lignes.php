<?php


require './includes/global.php';

$lignes = $ligneManager->getLignes();

$directions = [];

if(isset($_GET['ligne']))
{
    $directions = $ligneManager->getDirections($_GET['ligne']);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lignes</title>
</head>
<body>
    <div id="lig-button-container">
    
    <?php foreach( $lignes as $ligne ) : ?>
        <?php 
            $numLigne = htmlspecialchars($ligne['LIG_NUM']);    
        ?>
        <a href="?ligne=<?= $numLigne ?>" class="button-lig"> 
            Ligne <?= $numLigne ?> 
        </a>
    <?php endforeach;?>

    <?php if(!empty($directions)): ?>
        
        <h3>Choisissez votre direction pour la ligne <?=htmlspecialchars($_GET['ligne'])?></h3>
        
        <div id="dir-button-container">
            <?php foreach( $directions as $dir ) : ?>
                <?php $numDir = htmlspecialchars($dir); ?>
                <a href="horaires.php?direction=<?= $numDir ?>" class="button-dir"> 
                    Ligne <?= $numDir ?> 
                </a>
            <?php endforeach;?>
        </div>

    <?php endif;?>

    </div>
</body>
</html>