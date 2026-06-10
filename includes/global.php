<?php
require __DIR__ . '/session.php';
require __DIR__ . '/../modules/bdd.php';
require __DIR__ . '/../modules/auth.php';

$env = require_once __DIR__ . '/../env.php';

$database = new Database(
    $env['db_oracle'],
    $env['db_username'],
    $env['db_password']
);

$authentificator = new Authentificator(
    $database,
    $env['password_secret']
);

include_once 'footer.php';
include_once 'navBar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viking Transport</title>
    <link rel="stylesheet" href="global.css">
</head>
<body>

    <main> 