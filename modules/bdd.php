
<?php

class Database
{
    private $conn;

    public function __construct($dsn, $username, $password)
    {
        try {
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            $this->conn = null;
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function executeUpdate($sql)
    {
        return $this->conn->exec($sql);
    }

    public function prepareStatement($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function readData($sql, &$result)
    {
        $cur = $this->conn->query($sql);
        $result = $cur->fetchAll(PDO::FETCH_ASSOC);
        return count($result);
    }

    public function listLines()
    {
        $cur = $this->conn->query("SELECT * FROM vik_ligne");
        return $cur->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientReservationHistory($cliNum)
    {
        $sql = "SELECT * FROM vik_reservation JOIN vik_client USING (cli_num) WHERE cli_num = :num";
        $stmt = $this->prepareStatement($sql);
        $stmt->execute(['num' => $cliNum]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTripsAndTimesSameLine($codeInseeDepart, $codeInseeArrivee)
    {
        $sql = "SELECT LIG_NUM, 
                       TO_CHAR(ETA_HEURE, 'DD/MM/YYYY HH24:MI:SS') AS heure_depart,
                       ETA_DISTANCE AS distance
                FROM VIK_ETAPE
                WHERE COM_CODE_INSEE_DEPART = :depart 
                  AND COM_CODE_INSEE_ARRIVEE = :arrivee
                ORDER BY ETA_HEURE ASC";

        $stmt = $this->prepareStatement($sql);
        $stmt->execute(['depart' => $codeInseeDepart, 'arrivee' => $codeInseeArrivee]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShortestTripSameLine($codeInseeDepart, $codeInseeArrivee)
    {
        $sql = "SELECT LIG_NUM, 
                       ETA_DISTANCE,
                       TO_CHAR(ETA_HEURE, 'DD/MM/YYYY HH24:MI:SS') AS heure_voyage
                FROM VIK_ETAPE
                WHERE COM_CODE_INSEE_DEPART = :depart 
                  AND COM_CODE_INSEE_ARRIVEE = :arrivee
                ORDER BY ETA_DISTANCE ASC
                FETCH FIRST 1 ROWS ONLY";

        $stmt = $this->prepareStatement($sql);
        $stmt->execute(['depart' => $codeInseeDepart, 'arrivee' => $codeInseeArrivee]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFastestTripSameLine($codeInseeDepart, $codeInseeArrivee)
    {
        $sql = "SELECT LIG_NUM, 
                       TO_CHAR(ETA_HEURE, 'DD/MM/YYYY HH24:MI:SS') AS heure_voyage,
                       ETA_DISTANCE
                FROM VIK_ETAPE
                WHERE COM_CODE_INSEE_DEPART = :depart 
                  AND COM_CODE_INSEE_ARRIVEE = :arrivee
                ORDER BY ETA_HEURE ASC
                FETCH FIRST 1 ROWS ONLY"; // Use LIMIT 1 for MySQL/PostgreSQL if needed

        $stmt = $this->prepareStatement($sql);
        $stmt->execute(['depart' => $codeInseeDepart, 'arrivee' => $codeInseeArrivee]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listLineSchedules()
    {
        $cur = $this->conn->query("SELECT * FROM vik_ligne");
        return $cur->fetchAll(PDO::FETCH_ASSOC);
    }
    public function isUserAllowed($email, $password)
    {
        $sql = "SELECT * FROM vik_client WHERE cli_mail = :email AND cli_password = :password";
        $stmt = $this->prepareStatement($sql);
        $stmt->execute(['email' => $email, 'password' => $password]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        return $client ?: false;
    }
}
