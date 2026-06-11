<?php
include '../../includes/global.php'; 

// Récupération des infos de la session
$user = $session->getUserSession();
?>

<link rel="stylesheet" href="/assets/style/accueil.css">

<div class="welcome-container">
    
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar">
                <img src="../../image/jfanne.gif" alt="jfanne">
            </div>
            <h1>Bienvenue, <?= htmlspecialchars($user['CLI_PRENOM']) ?> !</h1>
            <p class="profile-role">Espace Membre Viking Transport</p>
        </div>

        <div class="profile-body">
            <div class="points-section">
                <span class="points-label">Votre nombre de point de fidélité</span>
                <div class="points-value">
                    <span class="number"><?= isset($user['CLI_POINTS']) ? intval($user['CLI_POINTS']) : 0 ?></span>
                    <span class="unit">pts</span>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-row">
                    <span class="detail-label">Nom complet</span>
                    <span class="detail-value"><?= htmlspecialchars($user['CLI_NOM']) ?> <?= htmlspecialchars($user['CLI_PRENOM']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Adresse Email</span>
                    <span class="detail-value"><?= htmlspecialchars($user['CLI_EMAIL'] ?? 'Non renseignée') ?></span>
                </div>
                <?php if(!empty($user['CLI_TELEPHONE'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Téléphone</span>
                    <span class="detail-value"><?= htmlspecialchars($user['CLI_TELEPHONE']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-footer">
            <button class="btn-logout" onclick="location.href = '/auth/logout'">Déconnexion</button>
        </div>
    </div>

</div>

<?php
require '../../includes/footer.php'; 
?>