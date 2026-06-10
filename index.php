<?php
// On appelle global.php qui va charger la session, le CSS et la navBar
include 'includes/global.php'; 
?>

<section class="hero">
    <h2>La puissance du transport <span>Nordique</span></h2>
    <p>Logistique lourde, fret routier et solutions de transport express. Nous déplaçons vos marchandises avec la force et la rigueur des Vikings.</p>
    <a href="#services" class="btn">Découvrir nos services</a>
</section>

<section id="services" class="services">
    <h3>Nos Services</h3>
    
    <div class="services-grid">
        <div class="service-card">
            <h4>Transport Routier</h4>
            <p>Une flotte moderne de camions pour acheminer vos marchandises partout en Europe.</p>
        </div>
        
        <div class="service-card">
            <h4>Fret Express</h4>
            <p>Vos livraisons urgentes gérées avec une rapidité absolue.</p>
        </div>
        
        <div class="service-card">
            <h4>Logistique Lourde</h4>
            <p>Convois exceptionnels. Rien n'est trop lourd pour nos drakkars.</p>
        </div>
    </div>
</section>

<?php
// On ferme la page avec le footer
require 'includes/footer.php'; 
?>