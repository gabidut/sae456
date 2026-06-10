<?php
include '../../includes/global.php'; 

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $authentificator->processAuth($email, $password);
        header("Location: /");
        exit();
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage();
    }
}
?>

<link rel="stylesheet" href="/assets/style/login_register.css">

<form class="login-main" method="POST">
    <div class="login-container">
        <h2>Connexion <span>Viking</span></h2>
        <p class="login-subtitle">Accédez à votre espace utilisateur</p>

        <form action="traitement_connexion.php" method="POST" class="login-form">
            
            <div class="form-group">
                <label for="username">E-Mail</label>
                <input type="email" id="username" name="email" placeholder="Ex: Porcq.tourDeFrance@unicaen.fr" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="**********" required>
            </div>

            <button type="submit" class="btn btn-login">Se connecter</button>
        </form>

        <div class="login-footer-links">
            <a href="#">Mot de passe oublié ?</a>
            <p>Pas encore de compte ? <a href="../register/">Créer un compte</a></p>
        </div>
    </div>
</div>

<?php
require '../../includes/footer.php'; 
?>