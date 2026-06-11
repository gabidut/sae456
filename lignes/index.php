<?php

require_once '../includes/global.php';

// 1. Récupération et filtrage des lignes
$lignesBrutes = $ligneManager->getLignes();
$lignes = [];
foreach ($lignesBrutes as $l) {
    $num = $l['LIG_NUM'];
    if (!isset($lignes[$num])) {
        $lignes[$num] = [
            'NUM' => $num,
            'LABEL' => "Ligne " . $num . " (" . $l['VILLE_DEB'] . " ➔ " . $l['VILLE_TERM'] . ")"
        ];
    }
}

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
    
    $ordreDesVilles = array_keys($grille); 
    $villeTerminus = !empty($ordreDesVilles) ? end($ordreDesVilles) : '';
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lignes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <h2>Consulter les Horaires Viking</h2>
    <p>Sélectionnez une ligne pour déployer ses options.</p>

    <div id="reseau-accordeon">
        
        <?php foreach( $lignes as $item ) : ?>
            <?php 
                $numLigne = htmlspecialchars($item['NUM']); 
                $labelLigne = htmlspecialchars($item['LABEL']); 
                $isLineActive = (isset($_GET['ligne']) && $_GET['ligne'] === $numLigne);
                
                $ancreLigne = "ligne-" . $numLigne;
            ?>
            
            <div id="<?= $ancreLigne ?>"></div>

            <a href="?ligne=<?= $numLigne ?>#<?= $ancreLigne ?>" class="button-lig <?= $isLineActive ? 'active-lig' : '' ?>"> 
                <?= $labelLigne ?> 
            </a>

            <?php if ($isLineActive && !empty($directions)): ?>
                <div class="directions-zone">
                    <p><em>Sélectionnez le sens de circulation :</em></p>
                    
                    <?php foreach( $directions as $dir ) : ?>
                        <?php 
                            $numDir = htmlspecialchars($dir['LIG_NUM']); // Ex: 1A
                            $nomTerminus = htmlspecialchars($dir['VILLE_TERMINUS']); // Ex: Cherbourg
                            $isDirActive = (isset($_GET['direction']) && $_GET['direction'] === $numDir);
                        ?>
                        <a href="?ligne=<?= $numLigne ?>&direction=<?= $numDir ?>#<?= $ancreLigne ?>" class="button-dir <?= $isDirActive ? 'active-dir' : '' ?>"> 
                            Sens : <?= $nomTerminus ?> 
                        </a>
                    <?php endforeach; ?> 

                    <?php if (isset($_GET['direction'])): ?>
                        <?php if (!empty($grille)): ?>
                            <div class="horaires-zone">
                                <h3>Direction finale : <span style="color: #28a745;"><?= htmlspecialchars($villeTerminus) ?></span></h3>
                                
                                <div class="route-timeline">
                                    <?php foreach ($ordreDesVilles as $index => $v) : ?>
                                        <div class="timeline-stop">
                                            <span class="stop-dot"></span>
                                            <span class="stop-name"><?= htmlspecialchars($v) ?></span>
                                        </div>
                                        <?php if ($index < count($ordreDesVilles) - 1): ?>
                                            <span class="timeline-line"></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <table>
                                    <thead>
                                        <tr>
                                            <th>Arrêt</th>
                                            <th colspan="25">Passages programmés</th>
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
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        <?php endforeach;?>

    </div>

</body>
</html>

<?php require '../includes/footer.php'; ?>