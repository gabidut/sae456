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
        
        <h3>Choisissez votre direction pour la ligne <?= htmlspecialchars($_GET['ligne']) ?></h3>
        
        <div id="dir-button-container">
            <?php foreach( $directions as $dir ) : ?>
                <?php $numDir = htmlspecialchars($dir); ?>
                
                <a href="?ligne=<?= htmlspecialchars($_GET['ligne']) ?>&direction=<?= $numDir ?>" class="button-dir"> 
                    Ligne <?= $numDir ?> 
                </a>
                
            <?php endforeach;?>
        </div>

    <?php endif;?>

    </div>

    <div id="table-horaire-lig">
    <?php if(!empty($grille)): ?>
        <hr>
        <h3>Grille horaire pour la direction <?= htmlspecialchars($_GET['direction']) ?></h3>
        
        <table border="1" style="border-collapse: collapse; cellpadding: 10px;">
            <thead>
                <tr>
                    <th>Ville / Arrêt</th>
                    <th colspan="20">Horaires de passage (Toute la journée)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grille as $nomVille => $listeHeures): ?> 
                    <tr>
                        <td><strong><?= htmlspecialchars($nomVille) ?></strong></td>
                        
                        <?php foreach ($listeHeures as $heure): ?>
                            <td><?= htmlspecialchars($heure) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>