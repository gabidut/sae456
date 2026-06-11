<?php
// 1. On charge global.php en premier.
include_once __DIR__ . '/../../includes/global.php'; 

// 2. Récupération sécurisée de la configuration et de la base de données
if (!isset($database) && isset($env)) {
    $database = new Database(
        $env['db_oracle'],
        $env['db_username'],
        $env['db_password']
    );
}

// 3. Vérification de sécurité
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

// 4. Récupération des clients 
$clients = [];
$error_msg = null;

if (isset($database) && $database->getConnection() !== null) {
    try {
        $pdo = $database->getConnection(); 
        
        // CORRECTION : On tente CLI_EMAIL au lieu de CLI_MAIL
        $query = "SELECT CLI_NUM, CLI_NOM, CLI_PRENOM, CLI_COURRIEL FROM VIK_CLIENT";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_msg = "Erreur SQL : " . $e->getMessage();
    }
} else {
    $error_msg = "Impossible de se connecter à la base de données.";
}
?>

<div class="container">
    
    <section class="admin-header">
        <h1>Espace Administration</h1>
        <p>Gestion et visualisation des comptes clients</p>
    </section>

    <section class="admin-content">
        <?php if ($error_msg): ?>
            <p class="error-message" style="color: red; font-weight: bold; background: #fff2f2; padding: 10px; border-radius: 4px; border: 1px solid #ffcccc;">
                <?php echo htmlspecialchars($error_msg); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($clients)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Numéro Client</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['CLI_NUM']); ?></td>
                            <td><?php echo htmlspecialchars($client['CLI_NOM']); ?></td>
                            <td><?php echo htmlspecialchars($client['CLI_PRENOM']); ?></td>
                            <td><?php echo htmlspecialchars($client['CLI_COURRIEL'] ?? $client['CLI_COURRIEL'] ?? ''); ?></td>
                            <td>
                                <a href="../modif/index.php?id=<?php echo $client['CLI_NUM']; ?>" class="btn-modifier">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucun compte client n'est enregistré pour le moment.</p>
        <?php endif; ?>
    </section>

</div>

<?php 
// 6. Inclusion du footer
require __DIR__ . '/../../includes/footer.php'; 
?>