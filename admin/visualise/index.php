<?php
$page_active = 'visulise'; 
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

// 1. Récupération de la liste des clients
$clients = $admin->listClients();

// 2. Est-ce qu'un client a été cliqué ?
$selected_client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;
$reservations = [];

if ($selected_client_id !== null) {
    $reservations = $authentificator->getReservation($selected_client_id);
}
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        
        <div class="viking-card container-users">
            <h1 class="viking-title">Gestion des utilisateurs</h1>
            <p class="subtitle text-muted">Liste globale des comptes clients enregistrés</p>
            
            <div class="table-responsive-wrapper">
                <table class="admin-table">
                    <thead class="sticky-header">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $c): ?>
                                <tr class="<?php echo ($selected_client_id !== null && $selected_client_id == $c['CLI_NUM']) ? 'selected-row-highlight' : ''; ?>">
                                    <td class="font-weight-bold text-red"><?php echo htmlspecialchars($c['CLI_NUM'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($c['CLI_NOM'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($c['CLI_PRENOM'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($c['CLI_COURRIEL'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <a href="?client_id=<?php echo $c['CLI_NUM']; ?>" class="btn-action-red">
                                            Voir Résas
                                        </a>
                                        <button type="button" class="btn-action-outline">
                                            Modifier
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-20 text-center text-muted">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="viking-card container-reservations">
            <h2 class="viking-title">Liste des réservations</h2>
            
            <?php if ($selected_client_id !== null): ?>
                <p class="subtitle text-muted">Réservations pour le client n°<strong class="text-red"><?php echo htmlspecialchars($selected_client_id); ?></strong> :</p>
                
                <div class="table-responsive-wrapper">
                    <table class="admin-table">
                        <thead class="sticky-header">
                            <tr>
                                <th>N° Résa</th>
                                <th>Date</th>
                                <th>Départ</th>
                                <th>Arrivée</th>
                                <th>Prix Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td class="font-weight-bold text-red"><?php echo htmlspecialchars($r['RES_NUM'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($r['RES_DATE'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($r['DEPART'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($r['ARRIVE'] ?? ''); ?></td>
                                        <td class="text-price font-weight-bold"><?php echo htmlspecialchars($r['RES_PRIX_TOT'] ?? '0'); ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-20 text-center text-muted font-italic">
                                        Ce client n'a pas encore effectué de réservation.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <p class="placeholder-info-box">
                    Veuillez cliquer sur le bouton "Voir Résas" d'un client ci-dessus pour charger son historique depuis Oracle.
                </p>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>