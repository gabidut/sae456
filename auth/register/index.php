<?php
include '../../includes/global.php';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($session->isUserLoggedIn()) {
            throw new Exception("Vous êtes déjà connecté");
        }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $departement = trim($_POST['departement'] ?? '');
        $ville = trim($_POST['ville'] ?? '');

        if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($phone) || empty($departement) || empty($ville)) {
            throw new Exception("Veuillez remplir tous les champs obligatoires.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide.");
        }

        $clean_phone = str_replace([' ', '.', '-', '+'], '', $phone);
        // On vérifie que c'est bien des chiffres et que la longueur n'est pas excessive (souvent 10 chiffres en France, jusqu'à 15 max pour l'international)
        if (!preg_match('/^[0-9]{10}$/', $clean_phone)) {
            throw new Exception("Le numéro de téléphone n'est pas valide. Il doit contenir 10 chiffres.");
        }

        if (!preg_match('/^[0-9]{1,2}$/', $departement)) {
            throw new Exception("Le département n'est pas valide. Il doit contenir 1 ou 2 chiffres maximum, sans lettres.");
        }

        $user = $authentificator->insertUser(
            $departement,
            $ville,
            $nom,
            $prenom,
            $authentificator->hash_password($password),
            $email,
            $phone
        );
        $session->setUserSession($user);
        header('Location: /auth/profile/');
        exit();
    } catch (Exception $e) {
        // Intercepte les erreurs spécifiques de validation ou de la base de données
        $error_message = $e->getMessage();
    }
}
?>

<link rel="stylesheet" href="/assets/style/login_register.css">

<main class="login-main">
    <div class="login-container register-wide">
        <h2>Inscription <span>Viking</span></h2>
        <p class="login-subtitle">Créez votre compte pour rejoindre le réseau</p>

        <form method="POST" class="login-form">

            <div class="form-grid">

                <div class="form-column">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Ex: Delhoumi" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Ex: Sylvian" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 06 20 74 58 80" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="departement-input">Département</label>
                        <input type="text" list="department" id="departement-input" name="departement" placeholder="Ex: 61" value="<?php echo htmlspecialchars($departement ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" list="villes" id="ville" name="ville" placeholder="Ex: Argentan" value="<?php echo htmlspecialchars($ville ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="********" required>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: Passoni@ergonomie.fr" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <? if (isset($error_message)) { ?>
                    <div class="form-group form-group-full">
                        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <? } ?>

            </div>

            <button type="submit" class="btn-login">Créer mon compte</button>
        </form>

        <div class="login-footer-links">
            <p>Déjà inscrit ? <a href="../login/">Se connecter</a></p>
        </div>
    </div>


    <script src="/assets/scripts/register.js"></script>
    <datalist id="villes">
        <?php foreach ($reservationManager->listCities() as $ville) : ?>
            <option value="<?php echo htmlspecialchars($ville['COM_NOM']); ?>"/>
        <?php endforeach; ?>
    </datalist>

    <datalist id="department">
        <?php foreach ($reservationManager->listDepartments() as $department) : ?>
            <option value="<?php echo htmlspecialchars($department['DEP_NUM']); ?>">
                <?php echo htmlspecialchars($department['DEP_NOM']); ?>
            </option>
        <?php endforeach; ?>
    </datalist>
</main>

<?php
require '../../includes/footer.php';
?>