<?php
$page_active = 'stats';
require_once __DIR__ . '/../../includes/global.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

$dateFinHTML = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-d');
$dateDebutHTML = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-d', strtotime('-30 days'));

$dateDebutYY = date('d/m/y', strtotime($dateDebutHTML)); 
$dateFinYY = date('d/m/y', strtotime($dateFinHTML));

$dateDebutYYYY = date('d/m/Y', strtotime($dateDebutHTML)); 
$dateFinYYYY = date('d/m/Y', strtotime($dateFinHTML));

$connexionsData = $admin->getNbClientConnecteEntre($dateDebutYY, $dateFinYY);
$nbConnectes = $connexionsData[0]['TOTAL_CLIENTS'] ?? 0; 

$lignesStats = $admin->lignesLesPlusUtilisees($dateDebutYYYY, $dateFinYYYY);
$topUsers = $admin->top10BestUsers();

$labelsLignes = [];
$dataPourcentages = [];

foreach ($lignesStats['usages'] as $ligne) {
    $labelsLignes[] = "Ligne " . $ligne['LIG_NUM'];
    $dataPourcentages[] = $ligne['POURCENTAGE'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin Viking</title>
    
    <link rel="stylesheet" href="/assets/style/sidebar.css">
    <link rel="stylesheet" href="/assets/style/admin.css"> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stats-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .kpi-card {
            background: linear-gradient(145deg, #1a1a1a, #111111);
            border-left: 4px solid #da1b23;
        }

        .kpi-title {
            color: #888888;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 1100px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        
        <div class="viking-card" style="margin-bottom: 25px;">
            <h1 class="viking-title">Tableau de Bord & Statistiques</h1>
            <p class="subtitle text-muted">Analyse de l'utilisation du réseau et de l'engagement client.</p>

            <form method="GET" action="" style="display: flex; gap: 15px; background: #111; padding: 15px; border-radius: 8px; border: 1px solid #222; align-items: flex-end;">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 0.85rem; font-weight: bold; color: #aaa;">Date de début</label>
                    <input type="date" name="date_debut" value="<?= htmlspecialchars($dateDebutHTML) ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #333; background: #222; color: white;">
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 0.85rem; font-weight: bold; color: #aaa;">Date de fin</label>
                    <input type="date" name="date_fin" value="<?= htmlspecialchars($dateFinHTML) ?>" style="padding: 10px; border-radius: 6px; border: 1px solid #333; background: #222; color: white;">
                </div>

                <button type="submit" class="btn-action-red" style="height: 42px; display: flex; align-items: center; justify-content: center;">
                    Actualiser les stats
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
                <h2 style="font-size: 1.2rem; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 10px;">🏆 Top 10 Voyageurs (Global)</h2>
                
                <div class="table-responsive-wrapper" style="height: 300px;">
                    <table class="admin-table">
                        <thead class="sticky-header">
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th class="text-center">ID</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topUsers)): ?>
                                <?php foreach ($topUsers as $index => $u): ?>
                                    <?php 
                                        $infoTop = $session->getClientInfoFromId($u['CLI_NUM']);
                                        if (!$infoTop) continue; 
                                    ?>
                                    <tr>
                                        <td style="color: #da1b23; font-weight: bold;"><?= $index + 1 ?></td>
                                        <td>
                                            <strong style="color: #fff;"><?= htmlspecialchars($infoTop['CLI_PRENOM'] . ' ' . $infoTop['CLI_NOM']) ?></strong>
                                        </td>
                                        <td class="text-center text-muted">#<?= htmlspecialchars($u['CLI_NUM']) ?></td>
                                        <td class="text-center">
                                            <a href="../visualise/index.php?client_id=<?= $u['CLI_NUM'] ?>" class="btn-action-outline" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;">
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">Aucun utilisateur trouvé.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const labelsJS = <?= json_encode($labelsLignes) ?>;
    const dataJS = <?= json_encode($dataPourcentages) ?>;

    const ctx = document.getElementById('lignesChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labelsJS,
            datasets: [{
                data: dataJS,
                backgroundColor: [
                    '#da1b23', 
                    '#8b0000', 
                    '#444444', 
                    '#ffffff', 
                    '#6c757d',
                    '#111111'  
                ],
                borderColor: '#141414', 
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right', 
                    labels: {
                        color: '#ffffff',
                        padding: 20,
                        font: { size: 13, family: 'Segoe UI' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ' : ' + context.raw + '%';
                        }
                    }
                }
            },
            cutout: '70%' 
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>