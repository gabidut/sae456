<?php
include '../../includes/global.php'; 

?>
<h1>Bienvenue <?=  $session->getUserSession()['CLI_PRENOM'] ?> !</h1>

<button onclick="location.href = '/auth/logout'">Déconnexion</button>
<?php
require '../../includes/footer.php'; 
?>