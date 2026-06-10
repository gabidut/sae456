<?php
    require_once 'includes/global.php';

    $cliTest = '69';

    /*if(!$session->isUserLoggedIn())
    {
        header('Location: index.php');
    }  */
    
    //$infoClient = $session->getClientInfoFromId($_SESSION['user'])
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newNom = trim($_POST['nom_famille']);
    $newPrenom = trim($_POST['prenom_client']);

    if (!empty($newNom) && !empty($newPrenom)) {
        
        $session->$auth->changeNom($cliTest, $newNom);
        $session->$auth->changePrenom($cliTest, $newPrenom);
        
        $messageSucces = "Les modifications ont bien été enregistrées";
    }
}

$infoClient = $session->getClientInfoFromId($cliTest);

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
            <h2>Mon Espace</h2>
            <span class="badge-client">Statut : <?= htmlspecialchars($infoClient['TYP_NOM']) ?></span>

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

                    <h3>Téléphone :</h3>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($infoClient['CLI_TELEPHONE'] ?? '')?>">

                    <br><br>
                    <input type="submit" class="btn-submit" value="Enregistrer les modifications">
                </form>
            </div>
        </div>
    <?php else: ?>
        <p>Client introuvable.</p>
    <?php endif; ?>
</body>
</html>