<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Fonction pour déterminer si un lien est actif
function is_active($page, $dir = '') {
    global $current_page, $current_dir;
    if ($dir != '') {
        return ($current_dir == $dir && $current_page == $page) ? 'active' : '';
    }
    return ($current_page == $page) ? 'active' : '';
}
?>

<header class="main-header">
    <div class="header-container">
        
        <div class="header-left">
            <a href="/">
                <img src="/image/car_vikingTransport.png" alt="Logo Viking Transport" class="header-logo-img">
            </a>
        </div>

        <div class="header-center">
            <a href="/">
                <h1 class="main-title">Viking<span>Transport</span></h1>
            </a>
        </div>

        <button class="burger-menu" id="burger" aria-label="Ouvrir le menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="header-right" id="nav-menu">
            <nav class="navBar">
                <ul class="nav-links">
                    <li><a href="/" class="<?php echo ($current_dir == 'htdocs' && $current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="/reservation" class="<?php echo ($current_dir == 'reservation') ? 'active' : ''; ?>">Réservation</a></li>
                    <li><a href="/carte.php" class="<?php echo ($current_page == 'carte.php') ? 'active' : ''; ?>">Réseau</a></li>
                    <li><a href="/lignes" class="<?php echo ($current_dir == 'lignes') ? 'active' : ''; ?>">Lignes</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if (!$authentificator->isLoggedIn()) { ?>
                    <a href="/auth/login" class="btn-contact">Connexion</a>
                <?php } else { ?>
                    <a href="/auth/profile" class="btn-contact">Mon compte</a>
                <?php } ?>
            </div>
        </div>

    </div>
</header>

<script>
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    burger.addEventListener('click', () => {
        burger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
</script>