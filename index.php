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
            <h4>Lorem ipsum dolor sit amet</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime adipisci maiores architecto eius beatae sunt odit exercitationem ducimus earum consequuntur error natus quam enim sequi provident cumque, qui, ut iusto!</p>
        </div>
        
        <div class="service-card">
            <h4>Lorem ipsum dolor sit amet</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime adipisci maiores architecto eius beatae sunt odit exercitationem ducimus earum consequuntur error natus quam enim sequi provident cumque, qui, ut iusto!</p>
        </div>
        
        <div class="service-card">
            <h4>Lorem ipsum dolor sit amet</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime adipisci maiores architecto eius beatae sunt odit exercitationem ducimus earum consequuntur error natus quam enim sequi provident cumque, qui, ut iusto!</p>
        </div>
    </div>
</section>

<?php
require 'includes/footer.php'; 
?>