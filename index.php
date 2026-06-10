<?php
// On inclut le fichier global pour avoir accès aux fonctions de structure
require_once 'global.php';

// On appelle le header en lui passant le titre de la page en paramètre
includeHeader("Viking Transport - Accueil");
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
            <p>Une flotte moderne de camions pour acheminer vos marchandises partout en Europe, en toute sécurité et dans les délais.</p>
        </div>
        
        <div class="service-card">
            <h4>Fret Express</h4>
            <p>Vos livraisons urgentes gérées avec une rapidité absolue et un suivi en temps réel de votre cargaison.</p>
        </div>
        
        <div class="service-card">
            <h4>Logistique Lourde</h4>
            <p>Convois exceptionnels et marchandises volumineuses. Rien n'est trop lourd pour les drakkars de notre flotte.</p>
        </div>
    </div>
</section>

<?php
// On appelle le footer pour fermer proprement la page
includeFooter();
?>
