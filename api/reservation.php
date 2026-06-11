<?php
$env = require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../modules/bdd.php';
require_once __DIR__ . '/../modules/reservation.php';
require_once __DIR__ . '/../modules/ligne.php';

$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);
$reservationManager = new Reservation($database);
$ligneManager = new Ligne($database);

header('Content-Type: application/json');

if (isset($_GET['ligne'])) {
    try {
        $steps = $ligneManager->getHoraire($_GET['ligne'] . 'A');
        echo json_encode($steps);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ligne parameter']);
}
