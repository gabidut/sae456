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
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; scroll-behavior: smooth; }
        
        /* Boutons de Lignes */
        .button-lig { display: block; padding: 14px 20px; margin: 10px 0; text-decoration: none; border-radius: 8px; font-weight: bold; background: #fff; color: #007bff; border: 2px solid #007bff; max-width: 450px; transition: all 0.2s; }
        .button-lig:hover { background: #007bff; color: white; }
        .active-lig { background: #007bff !important; color: white !important; box-shadow: 0 4px 10px rgba(0,123,255,0.2); }
        
        /* Boutons de Directions */
        .button-dir { display: inline-block; padding: 10px 18px; margin: 5px; text-decoration: none; border-radius: 6px; font-weight: bold; background: #fff; color: #28a745; border: 2px solid #28a745; transition: all 0.2s; }
        .button-dir:hover { background: #28a745; color: white; }
        .active-dir { background: #28a745 !important; color: white !important; box-shadow: 0 4px 10px rgba(40,167,69,0.2); }

        /* Zones de déploiement */
        .directions-zone { margin-left: 20px; padding-left: 20px; border-left: 3px dashed #007bff; margin-bottom: 20px; }
        .horaires-zone { margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        /* Frise */
        .route-timeline { display: flex; align-items: center; flex-wrap: wrap; margin: 20px 0; background: #f1f3f5; padding: 15px; border-radius: 6px; }
        .timeline-stop { display: flex; align-items: center; }
        .stop-dot { width: 10px; height: 10px; background: #28a745; border-radius: 50%; display: inline-block; }
        .stop-name { margin-left: 6px; font-weight: 600; font-size: 13px; color: #495057; }
        .timeline-line { height: 3px; width: 30px; background: #28a745; margin: 0 8px; display: inline-block; }
        
        /* Tableau */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        th { background: #f1f3f5; color: #495057; }
        tr:hover { background-color: #f8f9fa; }
    </style>
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
                        <a href="?ligne=..&ligne=<?= $numLigne ?>&direction=<?= $numDir ?>#<?= $ancreLigne ?>" class="button-dir <?= $isDirActive ? 'active-dir' : '' ?>"> 
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