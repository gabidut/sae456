<?php
$page_active = 'visulise'; 
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        <div class="container">
            <h1>Futur liste des user</h1>
            <p>Bientot la liste des users</p>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>