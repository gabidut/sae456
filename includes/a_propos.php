<?php
// On appelle global.php qui va charger la session, le CSS et la navBar
include 'global.php'; 
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Les Casseurs Codeurs</title>
</head>
<body>

    <main class="container">
        
        <section class="about-header">
            <img src="image/logo_CC.png" alt="Logo Les Casseurs Codeurs" class="about-logo">
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
                    <img src="image/elias.png" alt="Photo de Membre 1" class="member-photo">
                    <p class="member-name">Elias ALLIGNE</p>
                    <p class="member-role">Fondateur & Dev Back-End</p>
                </div>

                <div class="team-card">
                    <img src="chemin/vers/photo2.jpg" alt="Photo de Membre 2" class="member-photo">
                    <p class="member-name">Kevin ERNAULT</p>
                    <p class="member-role">Co-fondatrice & UI/UX Designer</p>
                </div>

                <div class="team-card">
                    <img src="image/armand.jpg" alt="Photo de Membre 3" class="member-photo">
                    <p class="member-name">Armand PIVERT</p>
                    <p class="member-role">Développeur Front-End</p>
                </div>

                <div class="team-card">
                    <img src="chemin/vers/photo4.jpg" alt="Photo de Membre 4" class="member-photo">
                    <p class="member-name">Victorien GAIGNE</p>
                    <p class="member-role">Chef de Projet</p>
                </div>

                <div class="team-card">
                    <img src="chemin/vers/photo5.jpg" alt="Photo de Membre 5" class="member-photo">
                    <p class="member-name">Victor ANGER--RENAULT</p>
                    <p class="member-role">Expert DevOps</p>
                </div>

                <div class="team-card">
                    <img src="chemin/vers/photo6.jpg" alt="Photo de Membre 6" class="member-photo">
                    <p class="member-name">Gabriel DUTEURTRE</p>
                    <p class="member-role">Développeuse Fullstack</p>
                </div>

                <div class="team-card">
                    <img src="chemin/vers/photo7.jpg" alt="Photo de Membre 7" class="member-photo">
                    <p class="member-name">Lorenzo COUTY</p>
                    <p class="member-role">Alternant Développeur</p>
                </div>

            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>

</body>
<?php
// On ferme la page avec le footer
require 'footer.php'; 
?>