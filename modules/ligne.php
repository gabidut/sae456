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
        $sql = "SELECT DISTINCT REGEXP_REPLACE(LIG_NUM, '[^0-9]', '') AS LIG_NUM
                FROM VIK_LIGNE l
                JOIN VIK_COMMUNE c ON l.COM_CODE_INSEE_DEBU = c.COM_CODE_INSEE OR l.COM_CODE_INSEE_TERM = c.COM_CODE_INSEE
            ORDER BY TO_NUMBER(REGEXP_REPLACE(l.LIG_NUM, '[^0-9]', '')) ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLignes2()
    {
        $sql =
            "select distinct lig_num
            from vik_noeud  no
            join vik_commune co on co.com_code_insee = no.com_code_insee_arret";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function findAllLinesByCity($cityName)
    {
        $sql = "SELECT distinct TRIM(lig_num) as lig_num from vik_noeud n
                join vik_commune c on c.com_code_insee = n.com_code_insee_arret 
                or c.com_code_insee = n.com_code_insee_suivant
                where TRIM(LOWER(com_nom)) = TRIM(LOWER(:cityName))";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cityName' => $cityName]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getDirections($numeroDeLigne)
    {
        $sql = "SELECT l.LIG_NUM, c.COM_NOM AS VILLE_TERMINUS
            FROM VIK_LIGNE l
            JOIN VIK_COMMUNE c ON l.COM_CODE_INSEE_TERM = c.COM_CODE_INSEE
            WHERE REGEXP_REPLACE(l.LIG_NUM, '[^0-9]', '') = :ligne";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['ligne' => $numeroDeLigne]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHoraire($numeroDeLigne)
    {

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

    public function getCitiesByDepartment($departmentId)
    {
        $sql = "SELECT DISTINCT c.COM_NOM
                FROM VIK_COMMUNE c
                JOIN VIK_DEPARTEMENT l ON c.DEP_NUM = l.DEP_NUM
                WHERE TRIM(UPPER(l.DEP_NUM)) = TRIM(UPPER(:departmentId))
                ORDER BY c.COM_NOM ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['departmentId' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
