<?php
$env = require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../modules/bdd.php';
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

header('Content-Type: application/json');

if (isset($_GET['citiesByDep'])) {
    $department = $_GET['citiesByDep'];
    $cities = $ligneManager->getCitiesByDepartment($department);
    echo json_encode($cities);
}

if(isset($_GET['citiesAndGPS'])) {
    $reservationManager = new Reservation($database, $session);
    $departments = $reservationManager->listCities();
    echo json_encode($departments);
}

if(isset($_GET['linesAndSteps'])) {
    $reservationManager = new Reservation($database, $session);
    $departments = $reservationManager->listCitiesAndTheirSteps();
    echo json_encode($departments);
}