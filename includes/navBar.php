<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<?php
if (!isset($_GET['BYPASS_DECO'])) {
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

        <div class="header-right" id="nav-menu">
            <nav class="navBar">
                <ul class="nav-links">
                    <li><a href="/" class="<?php echo (in_array($current_dir, ['', '/', '\\', 'htdocs']) && $current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="/reservation" class="<?php echo ($current_dir == 'reservation') ? 'active' : ''; ?>">Réservation</a></li>
                    <li><a href="/reseau" class="<?php echo ($current_dir == 'reseau') ? 'active' : ''; ?>">Réseau</a></li>
                    <li><a href="/lignes" class="<?php echo ($current_dir == 'lignes') ? 'active' : ''; ?>">Lignes</a></li>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true): ?>
                        <li><a href="/admin/visualise/" class="<?php echo ($current_dir == '/admin/visualise/') ? 'active' : ''; ?>">Admin</a></li>
                    <?php endif; ?>
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

<?php
}
?>