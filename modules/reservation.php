<?php

class Reservation
{
    private $database;
    private $sessionHelper;
    /**
     * Summary of __construct
     * @param Database $database
     * @param SessionHelper $sessionHelper
     */
    public function __construct($database, $sessionHelper)
    {
        $this->sessionHelper = $sessionHelper;
        $this->database = $database;
    }

    public function listCities(): array
    {
        $sql = "SELECT DISTINCT COM_NOM, COM_CODE_INSEE, COM_LAT, COM_LONG FROM vik_commune ORDER BY COM_NOM ASC";
        $stmt = $this->database->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listDepartments(): array
    {
        $sql = "SELECT DISTINCT DEP_NUM, DEP_NOM FROM vik_departement ORDER BY DEP_NOM ASC";
        $stmt = $this->database->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStepsOfLine($lineId): array
    {
        $sql = "SELECT c.COM_NOM AS VILLE_ARRET, TO_CHAR(n.NOE_HEURE_PASSAGE, 'HH24:MI') AS HEURE_PASSAGE
                FROM VIK_NOEUD n
                JOIN VIK_COMMUNE c ON n.COM_CODE_INSEE_ARRET = c.COM_CODE_INSEE
                WHERE TRIM(UPPER(n.LIG_NUM)) = TRIM(UPPER(:ligne))
                UNION ALL
                SELECT c.COM_NOM AS VILLE_ARRET, TO_CHAR(n.NOE_HEURE_PASSAGE + (n.NOE_DUREE_PROCHAIN/1440), 'HH24:MI') AS HEURE_PASSAGE
                FROM VIK_NOEUD n
                JOIN VIK_COMMUNE c ON n.COM_CODE_INSEE_SUIVANT = c.COM_CODE_INSEE
                WHERE TRIM(UPPER(n.LIG_NUM)) = TRIM(UPPER(:ligne))
                ORDER BY HEURE_PASSAGE ASC";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['ligne' => $lineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReservation($reservation)
    {
        //TODO
        if ($this->sessionHelper->isUserLoggedIn()) {
        } else {
        }
    }
}
