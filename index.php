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
            <h4>Réservation de trajet</h4>
            <p>Ce service vous permet de planifier votre itinéraire à l'avance et de bloquer votre place à bord. Que ce soit pour un trajet régulier ou un transport à la demande, il vous suffit d'indiquer votre point de départ, votre destination et l'heure souhaitée pour voyager l'esprit tranquille.</p>
        </div>
        
        <div class="service-card">
            <h4>Carte du réseau</h4>
            <p>La carte du réseau est l'outil idéal pour visualiser l'ensemble des lignes de transport en un coup d'œil. Elle vous permet de repérer facilement les correspondances, les arrêts principaux et les itinéraires possibles pour vous déplacer efficacement dans toute la région.</p>
        </div>
        
        <div class="service-card">
            <h4>Horaires des lignes</h4>
            <p>Ce service met à votre disposition les fiches horaires en temps réel de chaque ligne. Vous pouvez y consulter les heures de passage exactes à chaque arrêt, les fréquences des passages selon les jours de la semaine, ainsi que les éventuels changements ou retards.</p>
        </div>
    </div>
</section>

<?php
require 'includes/footer.php'; 
?>