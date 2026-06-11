<?php
// 1. On appelle global.php qui gère déjà la session, le CSS global et la navBar
include_once '../includes/global.php'; 

// 2. Vérification de sécurité (L'utilisateur est-il admin ?)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit();
}

// 3. Connexion à la base de données
$host = 'localhost';
$dbname = 'votre_base_de_donnees'; // À remplacer par le nom de ta base
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupération des utilisateurs qui ont le rôle 'client'
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, date_inscription FROM utilisateurs WHERE role = 'client'");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<div class="container">
    
    <section class="admin-header">
        <h1>Espace Administration</h1>
        <p>Gestion et visualisation des comptes clients</p>
    </section>

    <section class="admin-content">
        <?php if (count($clients) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['id']); ?></td>
                            <td><?php echo htmlspecialchars($client['nom']); ?></td>
                            <td><?php echo htmlspecialchars($client['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['date_inscription']); ?></td>
                            <td>
                                <a href="modif.php?id=<?php echo $client['id']; ?>" class="btn-modifier">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucun compte client n'est enregistré pour le moment.</p>
        <?php endif; ?>
    </section>

</div>

<?php 
// 5. Inclusion du footer pour fermer proprement la page
require '../includes/footer.php'; 
?>