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

class SessionHelper
{
    private $database;
    public function __construct($database)
    {
        $this->database = $database;
    }
    public function setUserSession(int $user)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $user;
    }

    public function getUserSession()
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }
        return $this->getClientInfoFromId($_SESSION['user']);
    }

    public function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public function clearUserSession()
    {
        unset($_SESSION['user']);
    }

    
    public function getClientInfoFromId($cliNum)
    {
        $sql = "SELECT * FROM vik_client WHERE cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['num' => $cliNum]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
