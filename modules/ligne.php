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

    public function findAllLinesByCity($cityName)
    {
        $sql = "SELECT DISTINCT REGEXP_REPLACE(LIG_NUM, '[^0-9]', '') AS LIG_NUM
                FROM VIK_LIGNE l
                JOIN VIK_COMMUNE c ON l.COM_CODE_INSEE_DEBU = c.COM_CODE_INSEE OR l.COM_CODE_INSEE_TERM = c.COM_CODE_INSEE
                WHERE LOWER(c.COM_NOM) = LOWER(:cityName)
                ORDER BY TO_NUMBER(REGEXP_REPLACE(LIG_NUM, '[^0-9]', '')) ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cityName' => $cityName]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

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

    public function getHoraire($numeroDeLigne) {

    $sql = "
        SELECT c.COM_NOM AS VILLE_ARRET, TO_CHAR(n.NOE_HEURE_PASSAGE, 'HH24:MI') AS HEURE_PASSAGE
        FROM VIK_NOEUD n
        JOIN VIK_COMMUNE c ON n.COM_CODE_INSEE_ARRET = c.COM_CODE_INSEE
        WHERE TRIM(UPPER(n.LIG_NUM)) = TRIM(UPPER(:direction1))

        UNION ALL

        SELECT c.COM_NOM AS VILLE_ARRET, 
               TO_CHAR(n.NOE_HEURE_PASSAGE + (n.NOE_DUREE_PROCHAIN / 1440), 'HH24:MI') AS HEURE_PASSAGE
        FROM VIK_NOEUD n
        JOIN VIK_LIGNE l ON n.LIG_NUM = l.LIG_NUM
        JOIN VIK_COMMUNE c ON l.COM_CODE_INSEE_TERM = c.COM_CODE_INSEE
        WHERE TRIM(UPPER(n.LIG_NUM)) = TRIM(UPPER(:direction2))
          AND n.COM_CODE_INSEE_SUIVANT = l.COM_CODE_INSEE_TERM

        ORDER BY HEURE_PASSAGE ASC
    ";

    $stmt = $this->database->prepareStatement($sql);
    
    $stmt->execute([
        'direction1' => $numeroDeLigne,
        'direction2' => $numeroDeLigne
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
