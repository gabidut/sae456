<?php
// 1. Chargement de l'environnement et des modules
$env = require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/../../modules/bdd.php';
require_once __DIR__ . '/../../modules/auth.php';
require_once __DIR__ . '/../../includes/session.php';

// 2. Initialisation de la base de données et des helpers
$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);

// ATTENTION : Si SessionHelper ou global.php ne fait pas de session_start(), 
// on le force ici pour être sûr que PHP puisse lire la session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new SessionHelper($database);
$authentificator = new Authentificator($database, $env['password_secret'], $session);

// 3. Vérification de sécurité modifiée
// On vérifie d'abord si l'utilisateur est bien connecté selon ton module auth
// SI ÇA BLOQUE TOUJOURS : Tu peux commenter temporairement ce bloc 'if' pour tester si le reste fonctionne.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Si tu es renvoyé ici, c'est que $_SESSION['role'] n'est pas égal à 'admin'.
    // Tu peux faire un : var_dump($_SESSION); die(); ici pour inspecter ta session si besoin.
    header('Location: ../../index.php');
    exit();
}

// 4. Récupération des clients (Requête SQL calée sur ta table vik_client)
$clients = [];
try {
    $pdo = $database->getConnection(); 
    $query = "SELECT CLI_NUM, CLI_NOM, CLI_PRENOM, CLI_MAIL FROM VIK_CLIENT";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

// 5. On appelle global.php (il est dans htdocs/includes/, donc deux niveaux plus haut)
include_once __DIR__ . '/../../includes/global.php'; 
?>

<div class="container">
    
    <section class="admin-header">
        <h1>Espace Administration</h1>
        <p>Gestion et visualisation des comptes clients</p>
    </section>

    <section class="admin-content">
        <?php if (isset($error_msg)): ?>
            <p class="error-message">Erreur lors du chargement : <?php echo htmlspecialchars($error_msg); ?></p>
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
                            <td><?php echo htmlspecialchars($client['CLI_MAIL']); ?></td>
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
// 7. Inclusion du footer (situé dans htdocs/includes/)
require __DIR__ . '/../../includes/footer.php'; 
?>