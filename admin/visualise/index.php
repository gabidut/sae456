<?php
$page_active = 'visulise'; 
include_once __DIR__ . '/../../includes/global.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'nom';
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

if ($search_type === 'inactifs') {
    $clients = $admin->listClientsInactifs();
} elseif (!empty($search_query)) {
    switch ($search_type) {
        case 'id':
            $clients = $admin->listClientsSortID($search_query);
            break;
        case 'nom':
            $clients = $admin->listClientsSortNom($search_query);
            break;
        case 'prenom':
            $clients = $admin->listClientsSortPrenom($search_query);
            break;
        case 'email':
            $clients = $admin->listClientsSortCourriel($search_query);
            break;
        case 'ville':
            $clients = $admin->listClientsSortVille($search_query);
            break;
        case 'rang':
            $clients = $admin->listClientsSortRang($search_query);
            break;
        default:
            $clients = $admin->listClients();
            break;
    }
} else {
    $clients = $admin->listClients();
}

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
            
            <form method="GET" action="" class="viking-search-form" style="display: flex; gap: 15px; margin-bottom: 25px; background: #141414; padding: 15px; border-radius: 8px; border: 1px solid #222222; align-items: flex-end; flex-wrap: wrap;">
    
    <?php if ($selected_client_id !== null): ?>
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($selected_client_id); ?>">
    <?php endif; ?>

    <div style="display: flex; flex-direction: column; gap: 5px;">
        <label for="search_type" style="font-size: 0.85rem; font-weight: bold; color: #ffffff;">Rechercher par :</label>
        <select name="search_type" id="search_type" onchange="toggleSearchInput(this.value)" style="padding: 10px 12px; border-radius: 6px; border: 1px solid #333333; background: #1f1f1f; font-weight: 500; color: #ffffff; cursor: pointer; height: 42px;">
            <option value="nom" <?php echo $search_type === 'nom' ? 'selected' : ''; ?>>Nom</option>
            <option value="prenom" <?php echo $search_type === 'prenom' ? 'selected' : ''; ?>>Prénom</option>
            <option value="id" <?php echo $search_type === 'id' ? 'selected' : ''; ?>>ID Client</option>
            <option value="email" <?php echo $search_type === 'email' ? 'selected' : ''; ?>>Adresse Email</option>
            <option value="ville" <?php echo $search_type === 'ville' ? 'selected' : ''; ?>>Ville</option>
            <option value="rang" <?php echo $search_type === 'rang' ? 'selected' : ''; ?>>Rang (Type)</option>
            <option value="inactifs" <?php echo $search_type === 'inactifs' ? 'selected' : ''; ?>>⚠️ Comptes inactifs (+2 ans)</option>
        </select>
    </div>

    <div id="search_query_container" style="display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px;">
        <label for="search_query" style="font-size: 0.85rem; font-weight: bold; color: #ffffff;">Terme à rechercher :</label>
        <input type="text" name="search_query" id="search_query" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Entrez votre recherche..." style="padding: 10px 12px; border-radius: 6px; border: 1px solid #333333; background: #1f1f1f; color: #ffffff; width: 100%; box-sizing: border-box; height: 42px;">
    </div>

    <div style="display: flex; gap: 8px; height: 42px;">
        <button type="submit" class="btn-action-red" style="padding: 0 20px; height: 100%; cursor: pointer; border: none; font-weight: bold; display: flex; align-items: center; justify-content: center;">
            Filtrer
        </button>
        
        <?php if (!empty($search_query) || $search_type === 'inactifs'): ?>
            <a href="?" class="btn-action-outline" style="text-decoration: none; padding: 0 15px; display: flex; align-items: center; justify-content: center; height: 100%; box-sizing: border-box;">
                Réinitialiser
            </a>
        <?php endif; ?>
    </div>
</form>
            
            <div class="table-responsive-wrapper">
                <table class="admin-table">
                    <thead class="sticky-header">
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Ville</th>
                            <th>Profil & Points</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $c): ?>
                                <tr class="<?php echo ($selected_client_id !== null && $selected_client_id == $c['CLI_NUM']) ? 'selected-row-highlight' : ''; ?>">
                                    
                                    <td class="font-weight-bold text-red">#<?php echo htmlspecialchars($c['CLI_NUM'] ?? '0'); ?></td>
                                    
                                    <td>
                                        <strong style="color: #f3f3f3ff;"><?php echo htmlspecialchars(($c['CLI_NOM'] ?? '') . ' ' . ($c['CLI_PRENOM'] ?? '')); ?></strong>
                                    </td>
                                    
                                    <td>
                                        <div><?php echo htmlspecialchars($c['CLI_COURRIEL'] ?? ''); ?></div>
                                    </td>
                                    
                                    <td><?php echo htmlspecialchars($c['CLI_VILLE'] ?? ''); ?></td>
                                    
                                    <td>
                                        <div style="font-size: 0.85em; color: #e9e9e9ff; font-weight: bold; margin-top: 4px;">
                                            <?php echo intval($c['CLI_NB_POINTS_EC'] ?? 0); ?> | <?php echo intval($c['CLI_NB_POINTS_TOT'] ?? 0); ?> pts
                                        </div>
                                    </td>

                                    <td>
                                        <?php if ($c['CLI_NUM'] == 0): ?>
                                            <span style="color: #64748b; font-weight: bold;">Système</span>
                                        <?php elseif ($admin->clientInactif($c['CLI_NUM'])): ?>
                                            <span style="color: #ef4444; font-weight: bold; background: #fee2e2; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;">Inactif</span>
                                        <?php else: ?>
                                            <span style="color: #10b981; font-weight: bold; font-size: 0.85rem;">Actif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center" style="white-space: nowrap;">
                                        
                                        <?php 
                                            $search_params = !empty($search_query) ? '&search_type='.$search_type.'&search_query='.urlencode($search_query) : '';
                                            if ($search_type === 'inactifs') $search_params = '&search_type=inactifs';
                                        ?>
                                        <a href="?client_id=<?php echo htmlspecialchars($c['CLI_NUM']) . $search_params; ?>" class="btn-action-red" style="margin-right: 5px;">
                                            Voir Résas
                                        </a>
                                        
                                        <?php if ($c['CLI_NUM'] == 0): ?>
                                            <button type="button" class="btn-action-outline" style="opacity: 0.5; cursor: not-allowed;" title="Le compte système ne peut pas être modifié" disabled>
                                                Modifier
                                            </button>
                                        <?php else: ?>
                                            <a href="/admin/modif_User/index.php?client_id=<?php echo htmlspecialchars($c['CLI_NUM']); ?>" class="btn-action-outline" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box;">
                                                Modifier
                                            </a>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="p-20 text-center text-muted">
                                    Aucun utilisateur ne correspond à ces critères.
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

<script>
function toggleSearchInput(val) {
    const input = document.getElementById('search_query');
    if (val === 'inactifs') {
        input.value = '';
        input.disabled = true;
        input.placeholder = "Pas de texte requis pour les inactifs";
    } else {
        input.disabled = false;
        input.placeholder = "Entrez votre recherche...";
    }
}
window.onload = function() {
    toggleSearchInput(document.getElementById('search_type').value);
};
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>



