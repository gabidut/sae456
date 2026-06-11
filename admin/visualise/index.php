<?php
// 1. Chargement de l'environnement et de la session
$env = require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/../../modules/bdd.php';
require_once __DIR__ . '/../../includes/session.php';

// 2. Initialisation de la base de données
$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);
$pdo = $database->getConnection();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Vérification de sécurité (Admin uniquement)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// 4. Variables d'affichage
$page_active = 'clients'; // Pour allumer le bouton "Gestion Clients" dans la sidebar
$clients = [];
$error_msg = null;

// 5. Requête SQL (Placeholder fonctionnel pour lister les clients)
try {
    // On récupère les clients de la base Oracle
    $sql = "SELECT CLI_NUM, CLI_NOM, CLI_PRENOM, CLI_COURRIEL FROM VIK_CLIENT ORDER BY CLI_NOM ASC";
    $stmt = $pdo->query($sql);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_msg = "Impossible de récupérer la liste des clients : " . $e->getMessage();
}

// 6. Inclusion du header global (CSS commun)
include_once __DIR__ . '/../../includes/global.php'; 
?>

<div class="admin-dashboard-layout">

    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        <div class="container">
            
            <section class="admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h1>Gestion des Clients</h1>
                    <p>Liste complète des utilisateurs enregistrés sur le serveur Oracle</p>
                </div>
                <button style="padding: 10px 15px; background-color: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    + Ajouter un client
                </button>
            </section>

            <?php if ($error_msg): ?>
                <p style="color: red; font-weight: bold; background: #fee2e2; padding: 15px; border-radius: 4px;"><?php echo htmlspecialchars($error_msg); ?></p>
            <?php endif; ?>

            <div style="background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px;">ID</th>
                            <th style="padding: 15px;">Nom</th>
                            <th style="padding: 15px;">Prénom</th>
                            <th style="padding: 15px;">Email</th>
                            <th style="padding: 15px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $c): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px; font-weight: bold; color: #64748b;"><?php echo htmlspecialchars($c['CLI_NUM']); ?></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($c['CLI_NOM']); ?></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($c['CLI_PRENOM']); ?></td>
                                    <td style="padding: 15px; color: #0284c7;"><?php echo htmlspecialchars($c['CLI_COURRIEL']); ?></td>
                                    <td style="padding: 15px; text-align: center;">
                                        <a href="../client/modifier.php?id=<?php echo $c['CLI_NUM']; ?>" style="text-decoration: none; background-color: #f1f5f9; color: #1e293b; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem; font-weight: 500; border: 1px solid #cbd5e1;">
                                            ✏️ Modifier
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">
                                    Aucun client trouvé dans la base de données.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<?php 
// 7. Inclusion du footer
require __DIR__ . '/../../includes/footer.php'; 
?>