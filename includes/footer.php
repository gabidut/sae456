<?php
if (!isset($_GET['BYPASS_DECO'])) {
?>

    <footer class="site-footer">
        <div class="footer-bottom">
            <img src="../../image/logo_noir.png" alt="Viking Transport" class="footer-logo">
            <p class="footer-copyright">&copy; <?php echo date("Y"); ?> Les Casseurs Codeurs. Tous droits réservés.</p>
            <a href="/../includes/a_propos.php" class="footer-link">À propos</a>
        </div>
    </footer>

<?php
}
?>