<?php
$page_active = 'gestion_lignes';
require_once __DIR__ . '/../../includes/global.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['ligne']) || empty($_GET['ligne'])) {
    header('Location: index.php'); 
    exit();
}

$ligNum = $_GET['ligne'];
$messageSucces = "";
$messageErreur = "";

$communes = $admin->getToutesLesCommunes();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['btn_add_noeud'])) {
        $codeArret = $_POST['code_arret'];
        $codeSuivant = !empty($_POST['code_suivant']) ? $_POST['code_suivant'] : null;
        
        $heurePassage = "01/01/2000 " . $_POST['heure_passage'] . ":00"; 
        
        $distance = !empty($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $duree = !empty($_POST['duree']) ? intval($_POST['duree']) : 0;

        if (!empty($codeArret) && !empty($_POST['heure_passage'])) {
            try {
                $admin->insertNoeud($ligNum, $codeArret, $codeSuivant, $heurePassage, $distance, $duree);
                $messageSucces = "Le nouvel arrêt a été ajouté au trajet !";
            } catch (Exception $e) {
                $messageErreur = "Erreur SQL : Vérifie que l'arrêt n'existe pas déjà pour cette heure.";
            }
        } else {
            $messageErreur = "La ville d'arrêt et l'heure de passage sont obligatoires.";
        }
    }

    if (isset($_POST['btn_update_horaire'])) {
        $codeArretToUpdate = $_POST['code_arret_hidden'];
        $nouvelleHeureStr = $_POST['nouvelle_heure'];

        if (!empty($nouvelleHeureStr)) {
            $nouvelleHeureObj = "01/01/2000 " . $nouvelleHeureStr . ":00";
            
            $admin->updateHoraire($ligNum, $codeArretToUpdate, $nouvelleHeureObj);
            $messageSucces = "L'horaire a été mis à jour avec succès.";
        }
    }
}

$noeudsExistants = $admin->getNoeudsParLigne($ligNum);
?>

<div class="admin-dashboard-layout">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <main class="admin-main-content">
        
        <div style="margin-bottom: 20px;">
            <a href="index.php" class="btn-action-outline" style="text-decoration: none;">← Retour aux lignes</a>
        </div>

        <div class="viking-card" style="margin-bottom: 25px;">
            <h1 class="viking-title">Trajet de la Ligne <span class="text-red"><?= htmlspecialchars($ligNum) ?></span></h1>
        </div>

        <?php if ($messageSucces): ?>
            <div class="msg-succes" style="background: rgba(16,185,129,0.1); color: #10b981; padding: 15px; margin-bottom: 20px;">✅ <?= $messageSucces ?></div>
        <?php endif; ?>
        <?php if ($messageErreur): ?>
            <div class="msg-succes" style="background: rgba(218,27,35,0.1); color: #da1b23; padding: 15px; margin-bottom: 20px;">⚠️ <?= $messageErreur ?></div>
        <?php endif; ?>

        <div class="viking-card" style="margin-bottom: 25px;">
            <h2>➕ Ajouter une étape au trajet</h2>
            
            <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 150px;">
                    <label>Arrêt Actuel *</label>
                    <select name="code_arret" style="width: 100%; padding: 8px;">
                        <option value="">-- Ville d'arrêt --</option>
                        <?php foreach ($communes as $ville): ?>
                            <option value="<?= htmlspecialchars($ville['COM_CODE_INSEE']) ?>"><?= htmlspecialchars($ville['COM_NOM']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label>Prochain Arrêt (Optionnel)</label>
                    <select name="code_suivant" style="width: 100%; padding: 8px;">
                        <option value="">-- Ville suivante --</option>
                        <?php foreach ($communes as $ville): ?>
                            <option value="<?= htmlspecialchars($ville['COM_CODE_INSEE']) ?>"><?= htmlspecialchars($ville['COM_NOM']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Heure *</label>
                    <input type="time" name="heure_passage" required style="padding: 8px;">
                </div>

                <div>
                    <label>Dist. (km)</label>
                    <input type="number" step="0.1" name="distance" placeholder="ex: 12.5" style="padding: 8px; width: 80px;">
                </div>

                <div>
                    <label>Durée (min)</label>
                    <input type="number" name="duree" placeholder="ex: 20" style="padding: 8px; width: 80px;">
                </div>

                <button type="submit" name="btn_add_noeud" class="btn-action-red" style="height: 35px;">Insérer</button>
            </form>
        </div>

        <div class="viking-card">
            <h2>⏱️ Horaires programmés</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Heure Actuelle</th>
                        <th>Ville d'Arrêt</th>
                        <th>Direction (Prochain Arrêt)</th>
                        <th>Nouvelle Heure</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($noeudsExistants)): ?>
                        <?php foreach ($noeudsExistants as $n): ?>
                            <tr>
                                <form method="POST" action="">
                                    <td style="font-weight: bold; font-size: 1.1rem;">
                                        <?= htmlspecialchars($n['HEURE_PASSAGE']) ?>
                                    </td>
                                    
                                    <td>
                                        <?= htmlspecialchars($n['VILLE_ARRET']) ?>
                                        <input type="hidden" name="code_arret_hidden" value="<?= htmlspecialchars($n['COM_CODE_INSEE_ARRET']) ?>">
                                    </td>

                                    <td class="text-muted">
                                        <?= htmlspecialchars($n['VILLE_SUIVANTE'] ?? 'Terminus') ?> 
                                        (<?= htmlspecialchars($n['NOE_DISTANCE_PROCHAIN']) ?> km)
                                    </td>

                                    <td>
                                        <input type="time" name="nouvelle_heure" value="<?= htmlspecialchars($n['HEURE_PASSAGE']) ?>" style="padding: 6px; background: #111; color: white; border: 1px solid #333;">
                                    </td>

                                    <td class="text-center">
                                        <button type="submit" name="btn_update_horaire" class="btn-action-outline">Sauvegarder l'heure</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">Aucun arrêt programmé pour cette ligne.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>