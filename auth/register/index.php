<?php
include '../../includes/global.php'; 
?>

<link rel="stylesheet" href="/assets/style/login_register.css">

<main class="login-main">
    <div class="login-container register-wide">
        <h2>Inscription <span>Viking</span></h2>
        <p class="login-subtitle">Créez votre compte pour rejoindre le réseau</p>

        <form action="traitement_inscription.php" method="POST" class="login-form">
            
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
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 07 80 39 44 67" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="departement">Département</label>
                        <input type="text" id="departement" name="departement" placeholder="Ex: Orne" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" placeholder="Ex: Argentan" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: Passoni@ergonomie.fr" required>
                </div>

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