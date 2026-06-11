<?php
// On définit la page active AVANT d'inclure la sidebar
$page_active = 'stats'; 

include_once __DIR__ . '/../../includes/global.php'; 
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        <h1>Tableau de bord - Statistiques</h1>
        <p>Ici, tu mettras tes graphiques Oracle ou tes compteurs !</p>
    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>