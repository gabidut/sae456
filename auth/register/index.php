<?php
include '../../includes/global.php';
$error_message = '';

if (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['phone']) && isset($_POST['departement']) && isset($_POST['ville'])) {
    try {
        if ($session->isUserLoggedIn()) {
            throw new Exception("Vous êtes déjà connecté");
        }
        $user = $authentificator->insertUser(
            $_POST['departement'],
            $_POST['ville'],
            $_POST['nom'],
            $_POST['prenom'],
            $authentificator->hash_password($_POST['password']),
            $_POST['email'],
            $_POST['phone'],
        );
        $session->setUserSession($user);
        header('Location: /auth/profile/');
        exit();
    } catch (Exception $e) {
        $error_message = "Une erreur est survenue lors de l'inscription.";
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
                        <input type="text" id="nom" name="nom" placeholder="Ex: Delhoumi" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Ex: Sylvian" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 06 20 74 58 80" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="departement-input">Département</label>
                        <input type="text" list="department" id="departement-input" name="departement" placeholder="Ex: 61" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" list="villes" id="ville" name="ville" placeholder="Ex: Argentan" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="********" required>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: Passoni@ergonomie.fr" required>
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