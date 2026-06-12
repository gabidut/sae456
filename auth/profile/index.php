<?php
require_once __DIR__ . '/../../includes/global.php';

page_requirements(true);

$cliId = $_SESSION['user'];
$infoClient = $session->getClientInfoFromId($cliId);
$reservation_cli = $authentificator->getReservation($cliId);
usort($reservation_cli, function ($a, $b) {
    return strtotime($b['RES_DATE']) - strtotime($a['RES_DATE']);
});

$messageSucces = "";
$messageErreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['btn_modif_info'])) {
        $newNom = trim($_POST['nom_famille']);
        $newPrenom = trim($_POST['prenom_client']);
        $newVille = trim($_POST['ville_client']);

        if (!empty($newNom) && !empty($newPrenom) && !empty($newVille)) {
            $authentificator->changeNom($cliId, $newNom);
            $authentificator->changePrenom($cliId, $newPrenom);
            $authentificator->changeVille($cliId, $newVille);
            $messageSucces = "Informations personnelles mises à jour.";
            $infoClient = $session->getClientInfoFromId($cliId); // Refresh data
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
                        $infoClient = $session->getClientInfoFromId($cliId); // Refresh data
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
        $actualPassword = trim($_POST['mdp_act']);
        $newPassword = trim($_POST['mdp_nouv']);

        if (!empty($actualPassword) && !empty($newPassword)) {
            // Need to get the current hash to verify
            $fullUser = $authentificator->getClientFromMail($infoClient['CLI_COURRIEL']);
            if ($authentificator->verify_password($actualPassword, $fullUser['CLI_MDP'])) {
                $authentificator->changePassword($cliId, $newPassword);
                $messageSucces = "Mot de passe modifié avec succès.";
            } else {
                $messageErreur = "Mot de passe actuel incorrect.";
            }
        } else {
            $messageErreur = "Veuillez remplir les deux champs de mot de passe.";
        }
    }
}
?>

<link rel="stylesheet" href="/assets/style/profil.css">

<div class="profile-container">
    <div class="profile-header">
        <div>
            <h1>Bienvenue, <span><?= htmlspecialchars($infoClient['CLI_PRENOM']) ?></span> !</h1>
            <p>Gérez vos informations et consultez vos réservations Viking Transport.</p>
        </div>
        <button class="btn-logout" onclick="location.href = '/auth/logout'">Déconnexion</button>
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
        <!-- Informations Personnelles -->
        <div class="info-card">
            <h2>Informations Personnelles</h2>
            <form method="post">
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
                <input type="submit" class="btn-submit" value="Enregistrer" name="btn_modif_info">
            </form>
        </div>

        <!-- Contact -->
        <div class="info-card">
            <h2>Contact</h2>
            <form method="post">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="mail_client" value="<?= htmlspecialchars($infoClient['CLI_COURRIEL']) ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($infoClient['CLI_TELEPHONE'] ?? '') ?>">
                </div>
                <input type="submit" class="btn-submit" value="Enregistrer" name="btn_modif_contact">
            </form>
        </div>

        <!-- Mot de passe -->
        <div class="info-card">
            <h2>Sécurité</h2>
            <form method="post">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="mdp_act" placeholder="*********">
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="mdp_nouv" placeholder="*********">
                </div>
                <input type="submit" class="btn-submit" value="Modifier le mot de passe" name="btn_modif_mdp">
            </form>
        </div>
    </div>

    <!-- Réservations -->
    <div class="reservations-section">
        <h2>Mes Réservations</h2>
        <div class="table-responsive">
            <?php if ($reservation_cli): ?>
                <table class="table-resa">
                    <thead>
                        <tr>
                            <th>N° Resa</th>
                            <th>Date</th>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Heure</th>
                            <th>Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservation_cli as $resa): ?>
                            <tr>
                                <td><span class="badge-res">#<?= htmlspecialchars($resa['RES_NUM']) ?></span></td>
                                <td><?= htmlspecialchars($resa['RES_DATE']) ?></td>
                                <td><?= htmlspecialchars($resa['DEPART']) ?></td>
                                <td><?= htmlspecialchars($resa['ARRIVE']) ?></td>
                                <td><?= htmlspecialchars($resa['HEURE_DEPART']) ?></td>
                                <td><?= number_format($resa['RES_PRIX_TOT'], 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-msg">Vous n'avez pas encore effectué de réservation.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>