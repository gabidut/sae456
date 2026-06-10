<?php
require __DIR__ . '/session.php';
require __DIR__ . '/../modules/bdd.php';
require __DIR__ . '/../modules/auth.php';
require __DIR__ . '/../modules/ligne.php';
require __DIR__ . '/../modules/reservation.php';

$env = require_once __DIR__ . '/../env.php';

$database = new Database(
        $env['db_oracle'],
        $env['db_username'],
        $env['db_password']
);

$session = new SessionHelper(
    $database,
);

$authentificator = new Authentificator(
        $database,
        $env['password_secret'],
    $session
);

$ligneManager = new Ligne($database);
$reservationManager = new Reservation($database, $session);


include __DIR__ . '/../includes/navBar.php';
// Démarrage du HTML commun
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viking Transport</title>
    <link rel="stylesheet" href="/assets/style/global.css">
</head>
<body>


    <main> 