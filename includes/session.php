<?php

session_start();

/**
 * @param $require_auth boolean
 * @return void
 */
function page_requirements(
    bool $require_auth = false
): void {
    if (!$_SESSION['user'] && $require_auth) {
        header('Location: /login.php');
        exit();
    }
}
