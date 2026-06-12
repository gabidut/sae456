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
        $sql = "SELECT cli_num,cli_nom,cli_prenom,cli_courriel,cli_telephone,cli_ville,cli_nb_points_ec,cli_nb_points_tot, typ_nom FROM vik_client  join vik_type_client USING (typ_num) WHERE cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['num' => $cliNum]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function setCurrentTripDetails($tripDetails)
    {
        $_SESSION['current_trip'] = $tripDetails;
    }

    public function getCurrentTripDetails()
    {
        return $_SESSION['current_trip'] ?? null;
    }

    public function setPointsUsed($points)
    {
        $_SESSION['points_used'] = (int)$points;
    }

    public function getPointsUsed()
    {
        return $_SESSION['points_used'] ?? 0;
    }

    public function clearPointsUsed()
    {
        unset($_SESSION['points_used']);
    }

    public function setTripDepartureTime($time)
    {
        $_SESSION['trip_departure_time'] = $time;
    }

    public function getTripDepartureTime()
    {
        return $_SESSION['trip_departure_time'] ?? null;
    }

    public function setAdminUser()
    {
        $_SESSION['is_admin'] = true;
    }

        public function unsetAdminUser()
    {
        $_SESSION['is_admin'] = false;
    }
}
