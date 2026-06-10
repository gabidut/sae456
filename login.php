<?php
include 'includes/global.php'; 
?>

<link rel="stylesheet" href="assets/style/login_register.css">

<div class="login-main">
    <div class="login-container">
        <h2>Connexion <span>Viking</span></h2>
        <p class="login-subtitle">Accédez à votre espace utilisateur</p>

        <form action="traitement_connexion.php" method="POST" class="login-form">
            
            <div class="form-group">
                <label for="username">Identifiant ou Email</label>
                <input type="text" id="username" name="username" placeholder="Ex: Porcq.tourDeFrance" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="......." required>
            </div>

            <button type="submit" class="btn btn-login">Se connecter</button>
        </form>

        <div class="login-footer-links">
            <a href="#">Mot de passe oublié ?</a>
            <p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
        </div>
    </div>
</div>

<?php
require 'includes/footer.php'; 
?>