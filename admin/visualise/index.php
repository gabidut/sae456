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
        
        <div class="container container-users">
            <h1>Gestion des utilisateurs</h1>
            <p class="subtitle">Bientôt la liste des users</p>
            
            <div class="table-responsive-wrapper layout-bg-white">
                <table class="admin-table table-collapse-white">
                    <thead class="sticky-header users-thead-bg">
                        <tr class="border-bottom-heavy text-left">
                            <th class="p-12">ID</th>
                            <th class="p-12">Nom</th>
                            <th class="p-12">Prénom</th>
                            <th class="p-12">Email</th>
                            <th class="p-12 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $c): ?>
                                <tr class="border-bottom-light <?php echo ($selected_client_id !== null && $selected_client_id == $c['CLI_NUM']) ? 'selected-row-highlight' : ''; ?>">
                                    <td class="p-12 font-weight-bold"><?php echo htmlspecialchars($c['CLI_NUM'] ?? '0'); ?></td>
                                    <td class="p-12"><?php echo htmlspecialchars($c['CLI_NOM'] ?? ''); ?></td>
                                    <td class="p-12"><?php echo htmlspecialchars($c['CLI_PRENOM'] ?? ''); ?></td>
                                    <td class="p-12"><?php echo htmlspecialchars($c['CLI_COURRIEL'] ?? ''); ?></td>
                                    <td class="p-12 text-center">
                                        
                                        <a href="?client_id=<?php echo $c['CLI_NUM']; ?>" class="btn-action-blue">
                                            Voir Résas
                                        </a>

                                        <button type="button" class="btn-action-gray">
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

        <div class="container container-reservations">
            <h2>Liste des réservations</h2>
            
            <?php if ($selected_client_id !== null): ?>
                <p class="subtitle">Réservations pour le client n°<strong><?php echo htmlspecialchars($selected_client_id); ?></strong> :</p>
                
                <div class="table-responsive-wrapper layout-bg-white">
                    <table class="admin-table table-collapse-white">
                        <thead class="sticky-header resas-thead-bg">
                            <tr class="border-bottom-heavy text-left">
                                <th class="p-12">N° Résa</th>
                                <th class="p-12">Date</th>
                                <th class="p-12">Départ</th>
                                <th class="p-12">Arrivée</th>
                                <th class="p-12">Prix Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach ($reservations as $r): ?>
                                    <tr class="border-bottom-light">
                                        <td class="p-12 font-weight-bold"><?php echo htmlspecialchars($r['RES_NUM'] ?? ''); ?></td>
                                        <td class="p-12"><?php echo htmlspecialchars($r['RES_DATE'] ?? ''); ?></td>
                                        <td class="p-12"><?php echo htmlspecialchars($r['DEPART'] ?? ''); ?></td>
                                        <td class="p-12"><?php echo htmlspecialchars($r['ARRIVE'] ?? ''); ?></td>
                                        <td class="p-12 text-price font-weight-bold"><?php echo htmlspecialchars($r['RES_PRIX_TOT'] ?? '0'); ?> €</td>
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
                    Veuillez cliquer sur le bouton "👁️ Voir Résas" d'un client en haut pour afficher l'historique de ses réservations Oracle.
                </p>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>