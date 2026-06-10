<?php
require __DIR__ . '/session.php';
require __DIR__ . '/../modules/bdd.php';
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