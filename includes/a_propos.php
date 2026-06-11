<?php
// On appelle global.php qui va charger la session, le CSS et la navBar
include_once '../includes/global.php'; 
?>

<div class="container">
    
    <section class="about-header">
        <img src="../image/logo_CC.png" alt="Logo Les Casseurs Codeurs" class="about-logo">
        <h1>Les Casseurs Codeurs</h1>
    </section>

    <section class="about-intro">
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
        </p>
        <p>
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
        </p>
    </section>

    <section class="team-section">
        <h2>Notre Équipe</h2>
        
        <div class="team-grid">
            
            <div class="team-card">
                <img src="../image/elias.png" alt="Photo de Membre 1" class="member-photo">
                <p class="member-name">Elias ALLIGNE</p>
                <p class="member-role">Développeur HTML/CSS/PHP</p>
            </div>

            <div class="team-card">
                <img src="../image/kevin.jpg" alt="Photo de Membre 2" class="member-photo">
                <p class="member-name">Kevin ERNAULT</p>
                <p class="member-role">Co-fondatrice</p>
            </div>

            <div class="team-card">
                <img src="../image/armand.jpg" alt="Photo de Membre 3" class="member-photo">
                <p class="member-name">Armand PIVERT</p>
                <p class="member-role">Développeur HTML/CSS/PHP</p>
            </div>

            <div class="team-card">
                <img src="../image/victorien.jpg" alt="Photo de Membre 4" class="member-photo">
                <p class="member-name">Victorien GAIGNE</p>
                <p class="member-role">Chef de Projet</p>
            </div>

            <div class="team-card">
                <img src="../image/victor.jpg" alt="Photo de Membre 5" class="member-photo">
                <p class="member-name">Victor ANGER--RENAULT</p>
                <p class="member-role">Animateur</p>
            </div>

            <div class="team-card">
                <img src="../image/gabriel.jpg" alt="Photo de Membre 6" class="member-photo">
                <p class="member-name">Gabriel DUTEURTRE</p>
                <p class="member-role">Père fondateur de l'équipe</p>
            </div>

            <div class="team-card">
                <img src="../image/lorenzo.jpg" alt="Photo de Membre 7" class="member-photo">
                <p class="member-name">Lorenzo COUTY</p>
                <p class="member-role">Développeur HTML/CSS/PHP</p>
            </div>

        </div>
    </section>

</div>

<?php 
require '../includes/footer.php'; 
?>