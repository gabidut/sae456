<?php 
// 7. On appelle global.php pour le CSS et la navBar
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}
?>

<div class="admin-dashboard-layout">

    <aside class="admin-sidebar">
        <div class="sidebar-title">Menu Admin</div>
        <nav class="sidebar-menu">
            <a href="../statistiques/index.php" class="sidebar-link">
                <span class="icon">📊</span> Statistiques
            </a>
            <a href="../visualise/index.php" class="sidebar-link active">
                <span class="icon">👥</span> Gestion Clients
            </a>
        </nav>
    </aside>

    <main class="admin-main-content">
        <div class="container">
            
            <section class="admin-header">
                <h1>Modifier le profil Client</h1>
                <p><a href="../visualise/index.php">← Retour à la liste des clients</a></p>
            </section>

            <section class="admin-content">
                <?php if ($error_msg): ?>
                    <p class="error-message" style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error_msg); ?></p>
                <?php endif; ?>
                <?php if ($success_msg): ?>
                    <p class="success-message" style="color: green; font-weight: bold;"><?php echo htmlspecialchars($success_msg); ?></p>
                <?php endif; ?>

                <?php if ($client): ?>
                    <form action="" method="POST" class="admin-form">
                        <div class="form-group">
                            <label>Numéro Client (Non modifiable) :</label>
                            <input type="text" value="<?php echo htmlspecialchars($client['CLI_NUM']); ?>" disabled class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="cli_nom">Nom :</label>
                            <input type="text" id="cli_nom" name="cli_nom" value="<?php echo htmlspecialchars($client['CLI_NOM']); ?>" required class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="cli_prenom">Prénom :</label>
                            <input type="text" id="cli_prenom" name="cli_prenom" value="<?php echo htmlspecialchars($client['CLI_PRENOM']); ?>" required class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="cli_mail">Email :</label>
                            <input type="email" id="cli_mail" name="cli_mail" value="<?php echo htmlspecialchars($client['CLI_MAIL']); ?>" required class="form-input">
                        </div>

                        <div class="form-actions" style="margin-top: 20px;">
                            <button type="submit" name="action_update" class="btn-save" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Enregistrer les modifications
                            </button>

                            <button type="submit" name="action_delete" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce client ?');" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                                Supprimer le compte
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>

        </div>
    </main>
</div>

<?php 
// 9. Inclusion du footer
require __DIR__ . '/../../includes/footer.php'; 
?>