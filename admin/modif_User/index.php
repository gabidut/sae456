<?php
require_once __DIR__ . '/../../includes/global.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['client_id'])) {
    header('Location: /admin/visualise/index.php');
    exit();
}

$cliId = intval($_GET['client_id']); 
$infoClient = $session->getClientInfoFromId($cliId);

if (!$infoClient) {
    header('Location: /admin/visualise/index.php');
    exit();
}

$messageSucces = "";
$messageErreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['btn_delete_client'])) {
        $admin->deleteClient($cliId);
        
        header('Location: /admin/visualise/index.php');
        exit();
    }

    if (isset($_POST['btn_modif_info'])) {
        $newNom = trim($_POST['nom_famille']);
        $newPrenom = trim($_POST['prenom_client']);
        $newVille = trim($_POST['ville_client']);

        if (!empty($newNom) && !empty($newPrenom) && !empty($newVille)) {
            $authentificator->changeNom($cliId, $newNom);
            $authentificator->changePrenom($cliId, $newPrenom);
            $authentificator->changeVille($cliId, $newVille);
            $messageSucces = "Informations personnelles mises à jour.";
            $infoClient = $session->getClientInfoFromId($cliId);
        } else {
            $messageErreur = "Tous les champs sont obligatoires.";
        }
    }

    if (isset($_POST['btn_modif_contact'])) {
        $newMail = trim($_POST['mail_client']);
        $newNum = trim($_POST['telephone']);

        if (!empty($newMail) && !empty($newNum)) {
            if (!filter_var($newMail, FILTER_VALIDATE_EMAIL)) {
                $messageErreur = "L'adresse email n'est pas valide.";
            } else {
                $clean_phone = str_replace([' ', '.', '-', '+'], '', $newNum);
                if (!preg_match('/^[0-9]{10}$/', $clean_phone)) {
                    $messageErreur = "Le numéro de téléphone n'est pas valide. Il doit contenir 10 chiffres.";
                } else {
                    try {
                        $authentificator->changeTel($cliId, $newNum);
                        $authentificator->changeMail($cliId, $newMail);
                        $messageSucces = "Coordonnées de contact mises à jour.";
                        $infoClient = $session->getClientInfoFromId($cliId);
                    } catch (Exception $e) {
                        $messageErreur = "Cette adresse email est peut-être déjà utilisée.";
                    }
                }
            }
        } else {
            $messageErreur = "Tous les champs sont obligatoires.";
        }
    }

    if (isset($_POST['btn_modif_mdp'])) {
        $newPassword = trim($_POST['mdp_nouv']);

        if (!empty($newPassword)) {
            $authentificator->changePassword($cliId, $newPassword);
            $messageSucces = "Mot de passe forcé et modifié avec succès.";
        } else {
            $messageErreur = "Veuillez remplir le nouveau mot de passe.";
        }
    }
}
?>

<link rel="stylesheet" href="/assets/style/profil.css">

<div class="profile-container">
    <div style="margin-bottom: 25px;">
        <a href="/admin/visualise/index.php" style="text-decoration: none; color: #a0a0a0; font-weight: 600; font-size: 0.95rem; display: inline-flex; align-items: center; transition: color 0.2s ease;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">
            ← Retour 
        </a>
    </div>

    <div class="profile-header">
        <div>
            <h1>Profil Utilisateur : <span><?= htmlspecialchars($infoClient['CLI_PRENOM']) ?> <?= htmlspecialchars($infoClient['CLI_NOM']) ?></span></h1>
            <p style="color: #6c757d; font-size: 14px; margin-top: 5px;">ID Client : #<?= $cliId ?></p>
        </div>

        <form method="post" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce client ? Cette action est irréversible et supprimera potentiellement ses réservations.');">
            <button type="submit" name="btn_delete_client" class="btn-logout" style="background-color: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                Supprimer le client
            </button>
        </form>
    </div>

    <?php if ($messageSucces): ?>
        <div class="msg-succes"><?= $messageSucces ?></div>
    <?php endif; ?>
    
    <?php if ($messageErreur): ?>
        <div class="msg-succes" style="background-color: rgba(229, 9, 20, 0.1); color: var(--accent-color); border-color: var(--accent-color);">
            <?= $messageErreur ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card status-card-<?= strtolower(preg_replace('/[^a-zA-Z0-0]/', '', $infoClient['TYP_NOM'])) ?>">
            <h3>Statut Membre</h3>
            <div class="stat-value"><?= htmlspecialchars($infoClient['TYP_NOM']) ?></div>
        </div>
        <div class="stat-card status-card-<?= strtolower(preg_replace('/[^a-zA-Z0-0]/', '', $infoClient['TYP_NOM'])) ?>">
            <h3>Points Fidélité (Total)</h3>
            <div class="stat-value"><?= intval($infoClient['CLI_NB_POINTS_TOT']) ?> pts</div>
        </div>
        <div class="stat-card status-card-<?= strtolower(preg_replace('/[^a-zA-Z0-0]/', '', $infoClient['TYP_NOM'])) ?>">
            <h3>Points Utilisables</h3>
            <div class="stat-value"><?= intval($infoClient['CLI_NB_POINTS_EC']) ?> pts</div>
        </div>
    </div>

    <div class="forms-grid">
        <div class="info-card">
            <h2>Informations Personnelles</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom_famille" value="<?= htmlspecialchars($infoClient['CLI_NOM']) ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom_client" value="<?= htmlspecialchars($infoClient['CLI_PRENOM']) ?>">
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="ville_client" value="<?= htmlspecialchars($infoClient['CLI_VILLE']) ?>">
                </div>
                <input type="submit" class="btn-submit" value="Enregistrer les infos" name="btn_modif_info">
            </form>
        </div>

        <div class="info-card">
            <h2>Contact</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="mail_client" value="<?= htmlspecialchars($infoClient['CLI_COURRIEL']) ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($infoClient['CLI_TELEPHONE'] ?? '') ?>">
                </div>
                <input type="submit" class="btn-submit" value="Enregistrer le contact" name="btn_modif_contact">
            </form>
        </div>

        <div class="info-card">
            <h2>Forcer un nouveau mot de passe</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="mdp_nouv" placeholder="Saisir le nouveau mot de passe">
                </div>
                <input type="submit" class="btn-submit" value="Écraser le mot de passe" name="btn_modif_mdp" style="background-color: #fd7e14; border-color: #fd7e14;">
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>