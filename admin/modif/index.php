<?php
// 1. Chargement de l'environnement et des modules (on remonte de deux dossiers)
$env = require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/../../modules/bdd.php';
require_once __DIR__ . '/../../modules/auth.php';
require_once __DIR__ . '/../../includes/session.php';

// 2. Initialisation de la base de données
$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Vérification de sécurité (Admin uniquement)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

$pdo = $database->getConnection();
$error_msg = null;
$success_msg = null;
$client = null;

// 4. RÉCUPÉRATION DU CLIENT (Via l'ID dans l'URL)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $cli_num = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT CLI_NUM, CLI_NOM, CLI_PRENOM, CLI_COURRIEL FROM VIK_CLIENT WHERE CLI_NUM = :id");
        $stmt->execute(['id' => $cli_num]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$client) {
            $error_msg = "Client introuvable.";
        }
    } catch (Exception $e) {
        $error_msg = "Erreur lors de la récupération : " . $e->getMessage();
    }
} else {
    $error_msg = "Aucun identifiant de client spécifié.";
}

// 5. ACTION : MODIFICATION DU COMPTE (Formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    try {
        $query = "UPDATE VIK_CLIENT SET CLI_NOM = :nom, CLI_PRENOM = :prenom, CLI_COURRIEL = :mail WHERE CLI_NUM = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'nom' => $_POST['cli_nom'],
            'prenom' => $_POST['cli_prenom'],
            'mail' => $_POST['CLI_COURRIEL'],
            'id' => $cli_num
        ]);
        
        $success_msg = "Le compte client a bien été mis à jour.";
        
        // On rafraîchit les données locales pour l'affichage
        $client['CLI_NOM'] = $_POST['cli_nom'];
        $client['CLI_PRENOM'] = $_POST['cli_prenom'];
        $client['CLI_COURRIEL'] = $_POST['CLI_COURRIEL'];
    } catch (Exception $e) {
        $error_msg = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// 6. ACTION : SUPPRESSION DU COMPTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete'])) {
    try {
        // Attention : Si le client a des réservations actives, Oracle peut bloquer à cause des clés étrangères.
        $query = "DELETE FROM VIK_CLIENT WHERE CLI_NUM = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $cli_num]);
        
        // Redirection vers la liste globale après suppression réussie
        header('Location: ../visualise/index.php?deleted=1');
        exit();
    } catch (Exception $e) {
        $error_msg = "Erreur lors de la suppression (le client a peut-être des réservations liées) : " . $e->getMessage();
    }
}

// 7. On appelle global.php pour le CSS et la navBar
include_once __DIR__ . '/../../includes/global.php'; 
?>

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
                    <label for="CLI_COURRIEL">Email :</label>
                    <input type="email" id="CLI_COURRIEL" name="CLI_COURRIEL" value="<?php echo htmlspecialchars($client['CLI_COURRIEL']); ?>" required class="form-input">
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

<?php 
// 9. Inclusion du footer
require __DIR__ . '/../../includes/footer.php'; 
?>