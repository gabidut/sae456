<?php
$page_active = 'stats';
require_once __DIR__ . '/../../includes/global.php';

// Sécurité Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

// 1. GESTION DES DATES
$dateFinHTML = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-d');
$dateDebutHTML = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-d', strtotime('-30 days'));

$dateDebutYY = date('d/m/y', strtotime($dateDebutHTML)); 
$dateFinYY = date('d/m/y', strtotime($dateFinHTML));
$dateDebutYYYY = date('d/m/Y', strtotime($dateDebutHTML)); 
$dateFinYYYY = date('d/m/Y', strtotime($dateFinHTML));

// 2. EXÉCUTION DES REQUÊTES
$connexionsData = $admin->getNbClientConnecteEntre($dateDebutYY, $dateFinYY);
$nbConnectes = $connexionsData[0]['TOTAL_CLIENTS'] ?? 0; 

$lignesStats = $admin->lignesLesPlusUtilisees($dateDebutYYYY, $dateFinYYYY);
$topUsers = $admin->top10BestUsers();

// 3. PRÉPARATION DONNÉES CHART.JS - LIGNES
$labelsLignes = [];
$dataPourcentages = [];
$numerosLignes = []; // Pour attribuer la bonne couleur en JS

foreach ($lignesStats['usages'] as $ligne) {
    $labelsLignes[] = "Ligne " . $ligne['LIG_NUM'];
    $dataPourcentages[] = $ligne['POURCENTAGE'];
    // On extrait juste le numéro (ou la lettre) pour le dictionnaire de couleurs
    $numerosLignes[] = preg_replace('/[^0-9]/', '', $ligne['LIG_NUM']); 
}

// 4. PRÉPARATION DONNÉES CHART.JS - TOP 5 CLIENTS (Bâtons)
$labelsTopUsers = [];
$dataTopUsers = [];
$topCount = 0;

foreach ($topUsers as $u) {
    if ($topCount >= 5) break; // On ne garde que les 5 meilleurs
    
    $infoTop = $session->getClientInfoFromId($u['CLI_NUM']);
    if ($infoTop) {
        // Ex: "Jean D." pour ne pas surcharger l'axe du graphique
        $nomCourt = htmlspecialchars($infoTop['CLI_PRENOM'] . ' ' . mb_substr($infoTop['CLI_NOM'], 0, 1) . '.');
        $labelsTopUsers[] = $nomCourt;
        // On récupère le fameux 'TOT' qu'on a ajouté dans la requête !
        $dataTopUsers[] = $u['TOT']; 
        $topCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin Viking</title>
    
    <link rel="stylesheet" href="/assets/style/sidebar.css">
    <link rel="stylesheet" href="/assets/style/admin.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stats-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kpi-card { background: linear-gradient(145deg, #1a1a1a, #111111); border-left: 4px solid #da1b23; }
        .kpi-title { color: #888888; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .kpi-value { font-size: 2.5rem; font-weight: 800; color: #ffffff; margin: 0; }
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media (max-width: 1100px) { .charts-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        
        <div class="viking-card" style="margin-bottom: 25px;">
            <h1 class="viking-title">Tableau de Bord & Statistiques</h1>
            
            <form method="GET" action="" style="display: flex; gap: 15px; background: #111; padding: 15px; border-radius: 8px; border: 1px solid #222; align-items: flex-end; margin-top: 20px;">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 0.85rem; font-weight: bold; color: #aaa;">Date de début</label>
                    <input type="date" name="date_debut" value="<?= htmlspecialchars($dateDebutHTML) ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #333; background: #222; color: white;">
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 0.85rem; font-weight: bold; color: #aaa;">Date de fin</label>
                    <input type="date" name="date_fin" value="<?= htmlspecialchars($dateFinHTML) ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #333; background: #222; color: white;">
                </div>

                <button type="submit" class="btn-action-red" style="height: 42px; display: flex; align-items: center; justify-content: center;">
                    Filtrer les stats
                </button>
            </form>
        </div>

        <div class="stats-kpi-grid">
            <div class="viking-card kpi-card">
                <div class="kpi-title">Clients connectés sur la période</div>
                <div class="kpi-value"><?= number_format($nbConnectes, 0, ',', ' ') ?></div>
            </div>
            
            <div class="viking-card kpi-card">
                <div class="kpi-title">Total des trajets réservés</div>
                <div class="kpi-value"><?= number_format($lignesStats['total'], 0, ',', ' ') ?></div>
            </div>
        </div>

        <div class="charts-grid">
            
            <div class="viking-card">
                <h2 style="font-size: 1.2rem; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 10px;">Répartition par ligne (%)</h2>
                <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="lignesChart"></canvas>
                </div>
            </div>

            <div class="viking-card">
                <h2 style="font-size: 1.2rem; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 10px;"> Top 5 Voyageurs (Réservations)</h2>
                <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    const vikingStatsData = {
        lignes: {
            labels: <?= json_encode($labelsLignes) ?>,
            data: <?= json_encode($dataPourcentages) ?>,
            numeros: <?= json_encode($numerosLignes) ?>
        },
        topUsers: {
            labels: <?= json_encode($labelsTopUsers) ?>,
            data: <?= json_encode($dataTopUsers) ?>
        }
    };
</script>

<script src="/assets/scripts/stats.js"></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>