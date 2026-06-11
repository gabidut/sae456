<?php
$page_active = 'visulise'; 
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

$clients = $admin->listClients();
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        <div class="container">
            <h1>Futur liste des user</h1>
            <p>Bientot la liste des users</p>
            
            <table class="admin-table" style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white;">
                <thead>
                    <tr style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0; text-align: left;">
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">Nom</th>
                        <th style="padding: 12px;">Prénom</th>
                        <th style="padding: 12px;">Email</th>
                        <th style="padding: 12px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $c): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($c['CLI_NUM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_NOM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_PRENOM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_COURRIEL']); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <button type="button" style="background-color: #f1f5f9; color: #1e293b; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem; border: 1px solid #cbd5e1; cursor: pointer;">
                                        ✏️ Modifier
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>