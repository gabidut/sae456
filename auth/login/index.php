<?php
include '../../includes/global.php'; 
$error_message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        if (empty($email) || empty($password)) {
            throw new AuthException("Veuillez remplir tous les champs.");
        }
        $authentificator->processAuth($email, $password);
        header("Location: /");
        exit();
    } catch (AuthException $e) {
        $error_message = $e->getMessage();
    } catch (Exception $e) {
        $error_message = "Une erreur inattendue est survenue.";
    }
}
?>

<link rel="stylesheet" href="/assets/style/login_register.css">

<main class="login-main">
    <div class="login-container">
        <h2>Connexion <span>Viking</span></h2>
        <p class="login-subtitle">Accédez à votre espace utilisateur</p>

        <?php if ($error_message): ?>
            <div class="error-banner">
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form" novalidate>
            
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" placeholder="Ex: Porcq.tourDeFrance@unicaen.fr" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo $error_message ? 'input-error' : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="**********" class="<?php echo $error_message ? 'input-error' : ''; ?>">
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <div class="login-footer-links">
            <a href="#">Mot de passe oublié ?</a>
            <p>Pas encore de compte ? <a href="../register/">Créer un compte</a></p>
        </div>
    </div>
</main>

<?php
require '../../includes/footer.php'; 
?>