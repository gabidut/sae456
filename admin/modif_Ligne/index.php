<?php
$page_active = 'gestion_lignes';
require_once __DIR__ . '/../../includes/global.php';

// Sécurité
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

$messageSucces = "";
$messageErreur = "";

// 1. On récupère TOUTES les communes (pour les menus déroulants)
$communes = $admin->getToutesLesCommunes();

// 2. On récupère TOUTES les lignes avec ta méthode !
$lignesBrutes = $ligneManager->getLignes(); 
$lignes = [];
// Petit filtrage au cas où Oracle renvoie "1A" et "1B" (on garde juste les numéros uniques)
foreach ($lignesBrutes as $l) {
    $num = $l['LIG_NUM'];
    if (!isset($lignes[$num])) {
        $lignes[$num] = $l;
    }
}

// 3. On vérifie si l'admin a cliqué sur une ligne en particulier
$selectedLigne = isset($_GET['ligne']) && $_GET['ligne'] !== '' ? $_GET['ligne'] : null;

// ==========================================
// TRAITEMENT DES FORMULAIRES POST (Pour la ligne sélectionnée)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedLigne !== null) {
    
    // --- AJOUTER UN NOUVEL ARRÊT ---
    if (isset($_POST['btn_add_noeud'])) {
        $codeArret = $_POST['code_arret'];
        $codeSuivant = !empty($_POST['code_suivant']) ? $_POST['code_suivant'] : null;
        $heurePassage = "01/01/2000 " . $_POST['heure_passage'] . ":00"; // Format Oracle
        $distance = !empty($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $duree = !empty($_POST['duree']) ? intval($_POST['duree']) : 0;

        if (!empty($codeArret) && !empty($_POST['heure_passage'])) {
            try {
                $admin->insertNoeud($selectedLigne, $codeArret, $codeSuivant, $heurePassage, $distance, $duree);
                $messageSucces = "Nouvel arrêt ajouté à la ligne " . $selectedLigne . " !";
            } catch (Exception $e) {
                $messageErreur = "Erreur : Cet arrêt existe peut-être déjà pour cette heure.";
            }
        }
    }

    // --- MODIFIER L'HORAIRE D'UN ARRÊT ---
    if (isset($_POST['btn_update_horaire'])) {
        $codeArretToUpdate = $_POST['code_arret_hidden'];
        $nouvelleHeureStr = $_POST['nouvelle_heure'];

        if (!empty($nouvelleHeureStr)) {
            $nouvelleHeureObj = "01/01/2000 " . $nouvelleHeureStr . ":00";
            $admin->updateHoraire($selectedLigne, $codeArretToUpdate, $nouvelleHeureObj);
            $messageSucces = "Horaire mis à jour avec succès.";
        }
    }
    
    // Pour éviter de renvoyer le formulaire en faisant F5
    if (!empty($messageSucces)) {
        header("Refresh: 2; URL=?ligne=" . $selectedLigne);
    }
}

// Si une ligne est sélectionnée, on charge ses arrêts
$noeudsExistants = [];
if ($selectedLigne !== null) {
    $noeudsExistants = $admin->getNoeudsParLigne($selectedLigne);
}
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        
        <div class="viking-card" style="margin-bottom: 25px;">
            <h1 class="viking-title">Gestion des Lignes & Horaires</h1>
            <p class="subtitle text-muted">Sélectionnez une ligne ci-dessous pour modifier son trajet.</p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px;">
                <?php foreach ($lignes as $l): ?>
                    <?php $isActive = ($selectedLigne == $l['LIG_NUM']); ?>
                    <a href="?ligne=<?= htmlspecialchars($l['LIG_NUM']) ?>" 
                       class="<?= $isActive ? 'btn-action-red' : 'btn-action-outline' ?>" 
                       style="text-decoration: none; padding: 10px 15px; font-size: 1rem;">
                        Ligne <?= htmlspecialchars($l['LIG_NUM']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($messageSucces): ?>
            <div class="msg-succes" style="background: rgba(16,185,129,0.1); color: #10b981; padding: 15px; margin-bottom: 20px; border-radius: 6px;">✅ <?= $messageSucces ?></div>
        <?php endif; ?>
        <?php if ($messageErreur): ?>
            <div class="msg-succes" style="background: rgba(218,27,35,0.1); color: #da1b23; padding: 15px; margin-bottom: 20px; border-radius: 6px;">⚠️ <?= $messageErreur ?></div>
        <?php endif; ?>

        <?php if ($selectedLigne !== null): ?>
            
            <div class="viking-card" style="margin-bottom: 25px; border-left: 4px solid #da1b23;">
                <h2>Édition du Trajet : Ligne <span style="color: #da1b23;"><?= htmlspecialchars($selectedLigne) ?></span></h2>
                <p class="text-muted">Départ : <?= htmlspecialchars($lignes[$selectedLigne]['VILLE_DEB']) ?> ➔ Terminus : <?= htmlspecialchars($lignes[$selectedLigne]['VILLE_TERM']) ?></p>
            </div>

            <div class="viking-card" style="margin-bottom: 25px;">
                <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 15px;">➕ Insérer un nouvel arrêt</h3>
                
                <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 150px;">
                        <label style="font-size: 0.85rem; color: #aaa;">Arrêt Actuel *</label>
                        <select name="code_arret" style="width: 100%; padding: 8px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                            <option value="">-- Ville d'arrêt --</option>
                            <?php foreach ($communes as $ville): ?>
                                <option value="<?= htmlspecialchars($ville['COM_CODE_INSEE']) ?>"><?= htmlspecialchars($ville['COM_NOM']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <label style="font-size: 0.85rem; color: #aaa;">Prochain Arrêt (Optionnel)</label>
                        <select name="code_suivant" style="width: 100%; padding: 8px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                            <option value="">-- Ville suivante --</option>
                            <?php foreach ($communes as $ville): ?>
                                <option value="<?= htmlspecialchars($ville['COM_CODE_INSEE']) ?>"><?= htmlspecialchars($ville['COM_NOM']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; color: #aaa;">Heure *</label><br>
                        <input type="time" name="heure_passage" required style="padding: 8px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; color: #aaa;">Dist. (km)</label><br>
                        <input type="number" step="0.1" name="distance" style="padding: 8px; width: 80px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; color: #aaa;">Durée (min)</label><br>
                        <input type="number" name="duree" style="padding: 8px; width: 80px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                    </div>

                    <button type="submit" name="btn_add_noeud" class="btn-action-red" style="height: 36px;">Ajouter</button>
                </form>
            </div>

            <div class="viking-card">
                <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 15px;">⏱️ Horaires programmés</h3>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Heure</th>
                            <th>Ville d'Arrêt</th>
                            <th>Direction (Prochaine ville)</th>
                            <th>Modifier Heure</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($noeudsExistants)): ?>
                            <?php foreach ($noeudsExistants as $n): ?>
                                <tr>
                                    <form method="POST" action="">
                                        <td style="font-weight: bold; font-size: 1.1rem; color: #da1b23;">
                                            <?= htmlspecialchars($n['HEURE_PASSAGE']) ?>
                                        </td>
                                        
                                        <td style="color: white; font-weight: 500;">
                                            <?= htmlspecialchars($n['VILLE_ARRET']) ?>
                                            <input type="hidden" name="code_arret_hidden" value="<?= htmlspecialchars($n['COM_CODE_INSEE_ARRET']) ?>">
                                        </td>

                                        <td class="text-muted">
                                            <?= htmlspecialchars($n['VILLE_SUIVANTE'] ?? 'Terminus') ?> 
                                            (<?= htmlspecialchars($n['NOE_DISTANCE_PROCHAIN'] ?? '0') ?> km)
                                        </td>

                                        <td>
                                            <input type="time" name="nouvelle_heure" value="<?= htmlspecialchars($n['HEURE_PASSAGE']) ?>" 
                                                   style="padding: 6px; background: #111; color: white; border: 1px solid #333; border-radius: 4px;">
                                        </td>

                                        <td class="text-center">
                                            <button type="submit" name="btn_update_horaire" class="btn-action-outline">Enregistrer</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted" style="padding: 30px;">Aucun arrêt programmé pour le moment.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="placeholder-info-box">
                Sélectionnez une ligne dans les boutons ci-dessus pour accéder à l'édition de ses horaires et arrêts.
            </div>
        <?php endif; ?>

    </main>
</div>