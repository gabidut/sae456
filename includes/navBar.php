<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">Viking<span>Transport</span></a>
        </div>

        <nav class="navBar">
            <ul class="nav-links">
                <li>
                    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="carte.php" class="<?php echo ($current_page == 'carte.php') ? 'active' : ''; ?>">
                        Réseau
                    </a>
                </li>
                <li>
                    <a href="lignes.php" class="<?php echo ($current_page == 'lignes.php') ? 'active' : ''; ?>">
                        Lignes
                    </a>
                </li>
                <li>
                    <a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">
                        Contact
                    </a>
                </li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="connexion.php" class="btn-contact">Connexion</a>
        </div>
    </div>
</header>