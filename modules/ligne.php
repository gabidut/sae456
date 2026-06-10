<?php

class Ligne
{
    private $database;
    /**
     * Summary of __construct
     * @param Database $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getLignes()
    {
        $sql = $sql =
            "SELECT DISTINCT REGEXP_REPLACE(LIG_NUM, '[^0-9]', '') AS LIG_NUM
            FROM VIK_LIGNE
            ORDER BY TO_NUMBER(REGEXP_REPLACE(LIG_NUM, '[^0-9]', '')) ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    // Ajoute cette fonction dans ta classe Ligne, juste en dessous de getLignes()
    public function getDirections($numeroDeLigne)
    {
        $sql = "SELECT LIG_NUM 
                FROM VIK_LIGNE 
                WHERE REGEXP_REPLACE(LIG_NUM, '[^0-9]', '') = :numero
                ORDER BY LIG_NUM ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['numero' => $numeroDeLigne]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
