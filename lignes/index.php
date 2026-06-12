<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/global.php';

// 1. Récupération et filtrage des lignes
$lignesBrutes = $ligneManager->getLignes();
$lignes = [];

foreach ($lignesBrutes as $l) {
    $num = $l['LIG_NUM'];

    // Si la ligne n'est pas encore initialisée, on crée sa structure
    if (!isset($lignes[$num])) {
        $lignes[$num] = [
            'NUM'    => $num,
            'ETAPES' => [] // On va stocker toutes les villes de la ligne ici
        ];
    }

    // On ajoute les villes de cette ligne si elles n'y sont pas déjà
    if (!in_array($l['VILLE_DEB'], $lignes[$num]['ETAPES'])) {
        $lignes[$num]['ETAPES'][] = $l['VILLE_DEB'];
    }
    if (!in_array($l['VILLE_TERM'], $lignes[$num]['ETAPES'])) {
        $lignes[$num]['ETAPES'][] = $l['VILLE_TERM'];
    }
}

// Construction des labels complets avec toutes les étapes séparées par des flèches
foreach ($lignes as $num => $donnees) {
    $chaineEtapes = implode(" ➔ ", $donnees['ETAPES']);
    $lignes[$num]['LABEL'] = "Ligne " . $num . " (" . $chaineEtapes . ")";
}

$directions = [];
$horaires = [];
$grille = [];

if (isset($_GET['ligne'])) {
    $directions = $ligneManager->getDirections($_GET['ligne']);
}

if (isset($_GET['direction'])) {
    $horaires = $ligneManager->getHoraire($_GET['direction']);

    foreach ($horaires as $h) {
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
    <title>Horaires des Lignes - Viking Transport</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.js"></script>
</head>

<body class="viking-theme-body">

    <main class="viking-main-container">

        <header class="viking-page-header">
            <h2 class="viking-title">Consulter les Horaires Viking</h2>
            <p class="subtitle text-muted">Sélectionnez une ligne pour déployer ses options et afficher la grille de passage.</p>
        </header>

        <div id="reseau-accordeon" class="accordion-container">

            <?php foreach ($lignes as $item) : ?>
                <?php
                $numLigne = htmlspecialchars($item['NUM']);
                $isLineActive = (isset($_GET['ligne']) && $_GET['ligne'] === $numLigne);
                $ancreLigne = "ligne-" . $numLigne;

                // Extraction de la première et de la dernière étape pour le bouton
                $villeDepart = !empty($item['ETAPES']) ? htmlspecialchars($item['ETAPES'][0]) : '';
                $villeArrivee = count($item['ETAPES']) > 1 ? htmlspecialchars(end($item['ETAPES'])) : '';
                ?>

                <div id="<?= $ancreLigne ?>" class="anchor-offset"></div>

                <a href="?ligne=<?= $numLigne ?>#<?= $ancreLigne ?>" class="button-lig <?= $isLineActive ? 'active-lig' : '' ?>">

                    <div class="line-badge-container">
                        <span class="line-badge"><?= $numLigne ?></span>
                    </div>

                    <div class="button-route-text">
                        <span class="route-city"><?= $villeDepart ?></span>
                        <span class="route-arrow">➔</span>
                        <span class="route-city"><?= $villeArrivee ?></span>
                    </div>
                </a>

                <?php if ($isLineActive && !empty($directions)): ?>
                    <div class="directions-zone">
                        <p class="direction-prompt"><em>Sélectionnez le sens de circulation :</em></p>

                        <div class="directions-buttons-group">
                            <?php foreach ($directions as $dir) : ?>
                                <?php
                                $numDir = htmlspecialchars($dir['LIG_NUM']);
                                $nomTerminus = htmlspecialchars($dir['VILLE_TERMINUS']);
                                $isDirActive = (isset($_GET['direction']) && $_GET['direction'] === $numDir);
                                ?>
                                <a href="?ligne=<?= $numLigne ?>&direction=<?= $numDir ?>#<?= $ancreLigne ?>" class="button-dir <?= $isDirActive ? 'active-dir' : '' ?>">
                                    Sens : <?= $nomTerminus ?>
                                </a>
                            <?php endforeach; ?>
                        </div> <?php if (isset($_GET['direction']) && !empty($grille)): ?>
                            <div class="horaires-zone" style="overflow-x: scroll;">
                                <h3>Direction finale : <span style="color: #ff1b1bff;"><?= htmlspecialchars($villeTerminus) ?></span></h3>
                                <button class="button-dir" onclick="printline()">Imprimer les horaires</button>
                                <div class="route-timeline">
                                    <?php foreach ($ordreDesVilles as $index => $v) : ?>
                                        <div class="timeline-stop">
                                            <?php if ($index === 0): ?>
                                                <img src="/image/car_vikingTransport.png" class="spinning-bus" alt="Bus">
                                            <?php else: ?>
                                                <span class="stop -dot"></span>
                                            <?php endif; ?>
                                            <span class="stop-name"><?= htmlspecialchars($v) ?></span>
                                        </div>
                                        <?php if ($index < count($ordreDesVilles) - 1): ?>
                                            <span class="timeline-line"></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="horaires-zone">
                                <h3 class="terminus-title">Direction finale : <span class="text-red"><?= htmlspecialchars($villeTerminus) ?></span></h3>

                                <div class="table-responsive-wrapper">
                                    <table class="viking-table" id="table-horaires">
                                        <thead>
                                            <tr>
                                                <th>Arrêt</th>
                                                <th class="text-center">Passages programmés</th>
                                            </tr>
                                        </thead>
                                        <tbody style="overflow-x: scroll;">
                                            <?php foreach ($grille as $nomVille => $listeHeures): ?>
                                                <tr>
                                                    <td class="cell-station-name"><strong><?= htmlspecialchars($nomVille) ?></strong></td>
                                                    <td>
                                                        <div class="hours-grid">
                                                            <?php foreach ($listeHeures as $heure): ?>
                                                                <span class="hour-tag"><?= htmlspecialchars($heure) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div> <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </main>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>

</html>
<script>
    function readQueryArgs() {
        const params = new URLSearchParams(window.location.search);
        return {
            ligne: params.get('ligne'),
            direction: params.get('direction')
        };
    }

    function printline() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('landscape');

        const queryArgs = readQueryArgs();
        const numLigne = queryArgs.ligne || "Inconnue";
        const direction = queryArgs.direction || "Inconnue";

        const logoLoc = "/image/car_vikingTransport.png";
        const img = new Image();
        img.src = logoLoc;

        img.onload = function() {
            const pageWidth = doc.internal.pageSize.getWidth();

            doc.setFillColor(229, 9, 20);
            doc.rect(0, 0, pageWidth, 40, 'F');

            doc.setTextColor(255, 255, 255);
            doc.setFontSize(22);
            doc.setFont("helvetica", "bold");
            doc.text("LIGNE : " + numLigne, 15, 25);

            doc.addImage(img, 'PNG', pageWidth - 80, 10, 614 / 10, 197 / 10);

            doc.setTextColor(40, 40, 40);
            doc.setFontSize(14);
            doc.text(`Direction : ${direction}`, 15, 55);

            doc.setDrawColor(200, 200, 200);
            doc.line(15, 60, pageWidth - 15, 60);
            doc.autoTable({
                html: '#table-horaires',
                startY: 65,
                theme: 'striped',
                styles: {
                    fontSize: 7,
                    cellPadding: 1.5,
                    halign: 'center'
                },
                columnStyles: {
                    0: {
                        halign: 'left',
                        minCellWidth: 30
                    }
                },
                headStyles: {
                    fillColor: [229, 9, 20],
                    textColor: [255, 255, 255]
                },
                margin: {
                    top: 65,
                    left: 10,
                    right: 10
                },
                horizontalPageBreak: true,
                horizontalPageBreakRepeat: 0
            });

            doc.setFont("helvetica", "italic");
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            const currentDate = new Date();
            const formattedDate = currentDate.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            doc.text(`Généré le ${formattedDate} - (c) Viking Transports`, 15, doc.internal.pageSize.getHeight() - 10);

            doc.save(`Horaires_Ligne_${numLigne}.pdf`);
        };

        img.onerror = function() {
            console.error("Impossible de charger le logo pour le PDF.");
        };
    }
</script>