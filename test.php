<?php
require_once "includes/global.php";

var_dump($database->insertUser(
    14,
    "Caen",
    "Jean",
    "Michel",
    $authentificator->hash_password("password"),
    "test@example.com",
    "01234"
));