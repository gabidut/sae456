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

$session = new SessionHelper(
    $database,
);

$authentificator = new Authentificator(
    $database,
    $env['password_secret'],
    $session
);

