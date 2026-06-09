<?php

session_start();

/**
 * @param $require_auth boolean
 * @return void
 */
function page_requirements(
    $require_auth = false
) {
    if(!$_SESSION['user'] && $require_auth) {
        header('Location: /login.php');
        exit();
    }
}