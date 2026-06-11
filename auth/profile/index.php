<?php
    require_once __DIR__ . '/../../includes/global.php';

    page_requirements(true); 

    $cliId = $_SESSION['user'];

    $infoClient = $session->getClientInfoFromId($_SESSION['user']);

    $reservation_cli = $authentificator->getReservation($cliId);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if(isset($_POST['btn_modif_info'])) {
            $newNom = trim($_POST['nom_famille']);
            $newPrenom = trim($_POST['prenom_client']);

                if (!empty($newNom) && !empty($newPrenom)) {
        
                $authentificator->changeNom($cliId, $newNom);
                $authentificator->changePrenom($cliId, $newPrenom);
                
                $messageSucces = "Les modifications ont bien été enregistrées";
            }
        }
        
        if(isset($_POST['btn_modif_contact']))
        {
            $newMail = trim($_POST['mail_client']);
            $newNum = trim($_POST['telephone']);

            if (!empty($newMail) && !empty($newNum)) {
        
                $authentificator->changeTel($cliId, $newNum);
                $authentificator->changeMail($cliId, $newMail);
                
                $messageSucces = "Les modifications ont bien été enregistrées";
            }
        }

        if(isset($_POST['btn_modif_mdp']))
        {
            $actualPassword = trim($_POST['mdp_act']);
            $newPassword = trim($_POST['mdp_nouv']);

            if (!empty($actualPassword) && !empty($newPassword)) {

                if($authentificator->verify_password($actualPassword, $user['CLI_MDP']))
                {
                    $authentificator->changePassword($_SESSION['user'] ,$newPassword);

                    $messageSucces = "Les modifications ont bien été enregistrées";
                }
                else{
                    $messageSucces = "Mot de passe incorecte";
                }
            }
        }
    
}



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
</head>
<body>
    <?php if($infoClient): ?>
        <div class="profile-card">
            <h1>Bienvenue <?=  $session->getUserSession()['CLI_PRENOM'] ?> !</h1>

            <!--Bouton a mettre en haut a gauche-->
            <button onclick="location.href = '/auth/logout'">Déconnexion</button>

            <div class="status-client-container">
                <span class="status-client">Statut : <?= htmlspecialchars($infoClient['TYP_NOM']) ?></span>
                <span class="point-tot-client">Statut : <?= htmlspecialchars($infoClient['CLI_NB_POINTS_TOT']) ?></span>
                <span class="point-ec-client">Statut : <?= htmlspecialchars($infoClient['CLI_NB_POINTS_EC']) ?></span>
            </div>

            <div class="info-card">
                <form method="post" action="">
                    <h2>Informations Personnelles</h2>

                    <?php if (isset($messageSucces)): ?>
                        <div class="msg-succes"><?= $messageSucces ?></div>
                    <?php endif; ?>

                    <h3>Nom :</h3>
                    <input type="text" name="nom_famille" value="<?= htmlspecialchars($infoClient['CLI_NOM'])?>">

                    <h3>Prénom :</h3>
                    <input type="text" name="prenom_client" value="<?= htmlspecialchars($infoClient['CLI_PRENOM'])?>">

                    <h3>Ville :</h3>
                    <input type="text" name="prenom_client" value="<?= htmlspecialchars($infoClient['CLI_VILLE'])?>">

                    <br><br>
                    <input type="submit" class="btn-submit" value="Enregistrer les modifications" name="btn_modif_info">
                </form>
            </div>

            <div class="info-card">
                <form method="post" action="">
                    <h2>Contacte</h2>

                    <?php if (isset($messageSucces)): ?>
                        <div class="msg-succes"><?= $messageSucces ?></div>
                    <?php endif; ?>

                    <h3>Mail :</h3>
                    <input type="text" name="mail_client" value="<?= htmlspecialchars($infoClient['CLI_COURRIEL'])?>">

                    <h3>Téléphone :</h3>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($infoClient['CLI_TELEPHONE'] ?? '')?>">

                    <br><br>
                    <input type="submit" class="btn-submit" value="Enregistrer les modifications" name="btn_modif_contact">
                </form>
            </div>

            <div class="mdp-card">
                <form method="post" action="">
                    <h2>Contacte</h2>

                    <?php if (isset($messageSucces)): ?>
                        <div class="msg-succes"><?= $messageSucces ?></div>
                    <?php endif; ?>

                    <h3>Mot de passe Actuel :</h3>
                    <input type="password" name="mdp_act" value="">

                    <h3>Nouveau Mot de passe :</h3>
                    <input type="password" name="mdp_nouv" value="">

                    <br><br>
                    <input type="submit" class="btn-submit" value="Enregistrer les modifications" name="btn_modif_mdp">
                </form>
            </div>

            <div class="table-resa">
                    <?php if($reservation_cli): ?>

                        <table style="border-collapse: collapse; cellpadding: 10px;">
                            <thead>
                                <tr>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservation_cli as $resa): ?>
                                    <tr>
                                        <td>
                                            <?php htmlspecialchars($resa['CLI_PRENOM'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['RES_NUM'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['RES_DATE'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['RES_PRIX_TOT'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['LIG_NUM'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['DEPART'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['ARRIVE'])?>
                                        </td>

                                        <td>
                                            <?php htmlspecialchars($resa['ETA_HEURE'])?>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                        
                    <?php else: ?>
                            <p>Aucune réservation.</p>
                    <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Client introuvable.</p>
    <?php endif; ?>
</body>
</html>

