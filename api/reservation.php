<?php
$env = require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../modules/bdd.php';
require_once __DIR__ . '/../modules/auth.php';

require_once __DIR__ . '/../modules/reservation.php';
require_once __DIR__ . '/../modules/ligne.php';
require_once __DIR__ . '/../includes/session.php';
$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);
$ligneManager = new Ligne($database);
$session = new SessionHelper($database);
$authentificator = new Authentificator($database, $env['password_secret'], $session);
$reservationManager = new Reservation($database, $session, $authentificator);


header('Content-Type: application/json');

if (isset($_GET['ligne'])) {
    try {
        $steps = $reservationManager->getStepsOfLine($_GET['ligne']);
        if (isset($_GET['grouped']) && ($_GET['grouped'] === '1' || strtolower($_GET['grouped']) === 'true')) {
            $grouped = [];
            foreach ($steps as $row) {
                $ville = $row['VILLE_ARRET'] ?? null;
                $time = $row['HEURE_PASSAGE'] ?? null;
                if (!$ville) continue;
                if (!isset($grouped[$ville])) $grouped[$ville] = [];
                if ($time && !in_array($time, $grouped[$ville], true)) $grouped[$ville][] = $time;
            }
            echo json_encode($grouped);
        } else {
            echo json_encode($steps);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if (isset($_GET['lignes'])) {
    try {
        $lignes = $ligneManager->getLignes2();
        echo json_encode($lignes);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if (isset($_GET['findAllLignesFromCity'])) {
    try {
        $lignes = $ligneManager->findAllLinesByCity($_GET['findAllLignesFromCity']);
        echo json_encode($lignes);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if (isset($_GET['getFinalHoraire']) && isset($_GET['lineId']) && isset($_GET['codeInseeDepart']) && isset($_GET['codeInseeArrivee']) && isset($_GET['horaireDepart'])) {
    try {
        $lignes = $reservationManager->getFinalHoraire($_GET['lineId'], $_GET['codeInseeDepart'], $_GET['codeInseeArrivee'], $_GET['horaireDepart']);
        echo json_encode($lignes);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}



if (isset($_POST['setTripDetails'])) {
    try {
        $tripDetails = json_decode($_POST['setTripDetails'], true);
        $session->setCurrentTripDetails($tripDetails);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
