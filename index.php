<?php
// On appelle global.php qui va charger la session, le CSS et la navBar
include 'includes/global.php'; 
?>

<section class="hero">
    <h2>La puissance du transport <span>Nordique</span></h2>
    <p>L'entreprise nordique qui vous emmenera là où vous avez besoin d'aller. Nous vous déplacons avec la force et la rigueur des Vikings.</p>
    <a href="#services" class="btn">Découvrir nos services</a>
</section>

<section id="services" class="services">
    <h3>Nos Services</h3>
    
    <div class="services-grid">
        <div class="service-card">
            <h4>TRAJETS OPTIMISÉS</h4>
            <p>Ne perdez plus une minute. Notre système intelligent calcule instantanément le trajet le plus rapide en temps réel. Moins d'attente, moins de bouchons, et une ponctualité garantie à chaque voyage !</p>
        </div>
    
        <div class="service-card">
                <h4>TRANSPORT NORMAND</h4>
                <p>Que ce soit pour vos déplacements professionnels, personnels ou touristiques, nous vous transportons partout en Normandie. Profitez d'un trajet confortable allant du Mont-Saint-Michel jusqu'à Rouen !</p>
            </div>
        
        <div class="service-card">
            <h4>PROGRAMME DE FIDÉLITÉ</h4>
            <p>Votre fidélité mérite d'être récompensée. Cumulez des points à chaque trajet effectué et profitez de réductions exclusives, de voyages gratuits et d'avantages réservés à nos membres inscrits !</p>
        </div>
    </div>
</section>

<?php
require 'includes/footer.php'; 
?>