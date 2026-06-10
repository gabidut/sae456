<?php
include 'includes/global.php'; 
?>

<link rel="stylesheet" href="assets/style/login_register.css">

<main class="login-main">
    <div class="login-container">
        <h2>Inscription <span>Viking</span></h2>
        <p class="login-subtitle">Créez votre compte pour rejoindre le réseau</p>

        <form action="traitement_inscription.php" method="POST" class="login-form">
            
            <div class="form-group">
                <label for="username">nom </label>
                <input type="text" id="username" name="username" placeholder="Ex: Delhoumi" required>
            </div>

            <div class="form-group">
                <label for="username">Prénom </label>
                <input type="text" id="username" name="username" placeholder="Ex: Sylvian" required>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" placeholder="Ex: Passoni@ergonomie.fr" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="........" required>
            </div>

            <div class="form-group">
                <label for="password">numéro de téléphone</label>
                <input type="password" id="password" name="password" placeholder="Ex: 07 80 39 44 67" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">département</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Orne" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">ville</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Argentan" required>
            </div>

            <button type="submit" class="btn-login">Créer mon compte</button>
        </form>

        <div class="login-footer-links">
            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        </div>
    </div>
</main>

<?php
require 'includes/footer.php'; 
?>