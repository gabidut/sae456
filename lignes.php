<?php
include 'includes/global.php'; 

?>

<div id="lig-button-container">
<?= $session->getUserSession()['CLI_PRENOM'] ?>
</div>

<?php
require 'includes/footer.php'; 
?>