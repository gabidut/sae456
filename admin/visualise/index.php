<?php
$page_active = 'visulise'; 
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

// 1. Récupération de la liste des clients
$clients = $auth->listClientsNum();

// 2. Est-ce qu'un client a été cliqué ?
$selected_client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;
$reservations = [];

if ($selected_client_id) {
    // On appelle ta fonction getReservation pour le client sélectionné
    $reservations = $auth->getReservation($selected_client_id);
}
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content" style="display: flex; flex-direction: column; gap: 40px; flex: 1;">
        
        <div class="container">
            <h1>Gestion des utilisateurs</h1>
            <p>Bientôt la liste des users</p>
            
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
                            <tr style="border-bottom: 1px solid #e2e8f0; <?php echo ($selected_client_id == $c['CLI_NUM']) ? 'background-color: #e0f2fe;' : ''; ?>">
                                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($c['CLI_NUM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_NOM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_PRENOM']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($c['CLI_COURRIEL']); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    
                                    <a href="?client_id=<?php echo $c['CLI_NUM']; ?>" style="text-decoration: none; background-color: #3b82f6; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9rem; margin-right: 5px;">
                                        👁️ Voir Résas
                                    </a>

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

        <div class="container" style="border-top: 2px dashed #cbd5e1; padding-top: 20px;">
            <h2>Liste des réservations</h2>
            
            <?php if ($selected_client_id): ?>
                <p>Réservations pour le client n°<strong><?php echo htmlspecialchars($selected_client_id); ?></strong> :</p>
                
                <table class="admin-table" style="width: 100%; border-collapse: collapse; margin-top: 15px; background: white;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 12px;">N° Résa</th>
                            <th style="padding: 12px;">Date</th>
                            <th style="padding: 12px;">Départ</th>
                            <th style="padding: 12px;">Arrivée</th>
                            <th style="padding: 12px;">Prix Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reservations)): ?>
                            <?php foreach ($reservations as $r): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($r['RES_NUM']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($r['RES_DATE']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($r['DEPART']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($r['ARRIVE']); ?></td>
                                    <td style="padding: 12px; color: #16a34a; font-weight: bold;"><?php echo htmlspecialchars($r['RES_PRIX_TOT']); ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                                    Ce client n'a pas encore effectué de réservation.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <p style="color: #94a3b8; font-style: italic; background-color: #f8fafc; padding: 20px; border-radius: 6px; border: 1px dashed #e2e8f0;">
                    Veuillez cliquer sur le bouton "👁️ Voir Résas" d'un client en haut pour afficher l'historique de ses réservations Oracle.
                </p>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>