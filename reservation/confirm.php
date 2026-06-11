<?php
include '../includes/global.php';

$reservation = $session->getCurrentTripDetails();
try {
    $resr = $reservationManager->createReservation($reservation);
} catch (Exception $e) {
    echo "Erreur lors de la création de la réservation : " . $e->getMessage();
    exit;
}
?>

<link rel="stylesheet" href="/assets/style/reservation-confirm.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="/assets/scripts/reservation-confirm.js" defer></script>

<div class="confirm-container">
    <div class="confirm-card">
        <div class="success-icon">OK</div>
        
        <h2 class="confirm-title">Réservation Confirmée !</h2>
        <p class="confirm-subtitle">Merci <?= $session->isUserLoggedIn() ? htmlspecialchars($session->getUserSession()['CLI_PRENOM']) : 'cher client' ?> pour votre confiance.</p>

        <div class="ticket-info">
            <div class="info-item">
                <label>N° Réservation</label>
                <span><?= htmlspecialchars($resr['cliNum']) ?>/<?= htmlspecialchars($resr['reservation_id']) ?></span>
            </div>
            <div class="info-item">
                <label>Heure de Départ</label>
                <span><?= htmlspecialchars($resr['etapes'][0]['heure']) ?></span>
            </div>
            <div class="info-item">
                <label>Points Gagnés</label>
                <span><?= htmlspecialchars($resr['points']) ?> pts</span>
            </div>
            <div class="info-item">
                <label>Montant Total</label>
                <span><?= number_format($resr['prix'], 2, ',', ' ') ?> €</span>
            </div>
        </div>

        <div class="qr-section">
            <div id="qrcode"></div>
            <p class="qr-text">Présentez ce QR Code au conducteur lors de l'embarquement.</p>
        </div>

        <div class="actions-confirm">
            <button id="download-billet" class="btn-confirm btn-primary-confirm">
                Télécharger le Billet (PDF)
            </button>
            <a href="/" class="btn-confirm btn-secondary-confirm">Retour à l'accueil</a>
        </div>
    </div>
</div>

<span id="reservation" style="display: none;">
    <?= json_encode($resr) ?>
</span>

<?php
require '../includes/footer.php';
?>