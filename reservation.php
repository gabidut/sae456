<?php
include 'includes/global.php';
require_once 'modules/bdd.php';

// On imagine que tu as tes identifiants de connexion quelque part ou que tu les passes ici
// Pour l'exemple, j'instancie la classe Database (à adapter selon ta config réelle)
// $db = new Database($dsn, $user, $password); 
// Mais comme je ne connais pas tes variables de connexion, je prépare le terrain.

// Simulation de récupération des villes (à lier avec ta vraie instance $db plus tard)
$villes = [];
if (isset($db)) {
    $villes = $db->listCities();
} else {
    // Fallback pour que la page s'affiche même sans BDD configurée ici
    $villes = [
        ['COM_NOM' => 'Oslo'],
        ['COM_NOM' => 'Stockholm'],
        ['COM_NOM' => 'Copenhague'],
        ['COM_NOM' => 'Helsinki'],
        ['COM_NOM' => 'Reykjavik']
    ];
}
?>

<div class="reservation-container">
    <h2>Réserver un Voyage</h2>
    
    <form action="traitement_reservation.php" method="POST" class="reservation-form">
        <div class="form-group">
            <label for="depart">Ville de Départ</label>
            <input list="villes-list" name="depart" id="depart" placeholder="Tapez le nom d'une ville..." required>
        </div>

        <div class="form-group">
            <label for="arrivee">Ville d'Arrivée</label>
            <input list="villes-list" name="arrivee" id="arrivee" placeholder="Tapez le nom d'une ville..." required>
        </div>

        <div class="form-group">
            <label for="date_voyage">Date du voyage</label>
            <input type="date" name="date_voyage" id="date_voyage" required>
        </div>

        <datalist id="villes-list">
            <?php foreach ($villes as $ville) : ?>
                <option value="<?php echo htmlspecialchars($ville['COM_NOM']); ?>">
            <?php endforeach; ?>
        </datalist>

        <button type="submit" class="btn btn-full">Rechercher des trajets</button>
    </form>
</div>

<?php
require 'includes/footer.php';
?>