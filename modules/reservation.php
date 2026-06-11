<?php

use LDAP\Result;

class Reservation
{
    private $database;
    private $sessionHelper;
    private $authentificator;
    /**
     * Summary of __construct
     * @param Database $database
     * @param SessionHelper $sessionHelper
     */
    public function __construct($database, $sessionHelper, $authentificator)
    {
        $this->sessionHelper = $sessionHelper;
        $this->database = $database;
        $this->authentificator = $authentificator;
    }

    public function listCities(): array
    {
        $sql = "SELECT DISTINCT COM_NOM FROM vik_commune ORDER BY COM_NOM ASC";
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
        $distance = 0;
        $minutes = 0;
        $villeDepart = $reservation['ville_depart'];
        $villeArrivee = $reservation['ville_arrivee'];
        if ($this->sessionHelper->isUserLoggedIn()) {
            $client = $this->sessionHelper->getUserSession();
            $cliNum = $client['cli_num'];
            while ($villeDepart !== $villeArrivee) {
                $sql = "select noe_distance_prochain, noe_duree_prochain, com_code_insee_suivant from vik_noeud where com_code_insee_arret = :villeDepart and lig_num = :ligNum";
                $stmt = $this->database->prepareStatement($sql);
                $stmt->execute(['villeDepart' => $villeDepart, 'ligNum' => $reservation['ligne']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $distance += $result['ETA_DISTANCE'];
                    $minutes += $result['ETA_DUREE_PROCHAIN'];
                    $villeDepart = $result['COM_CODE_INSEE_SUIVANT'];
                }
            }
            $this->authentificator->ajoutPointsApresResa($cliNum, $distance);

            if ($distance < 10){
                $tarNum = 1;
            }else if($distance < 20){
                $tarNum = 2;

            }else if($distance < 30){
                $tarNum = 3;
                
            }else if($distance < 40){
                $tarNum = 4;
                
            }else if( $distance < 50){
                $tarNum = 5;
                
            }else if($distance < 60){
                $tarNum = 6;
                
            }else if($distance < 80){
                $tarNum = 7;
                
            }else if($distance < 100){
                $tarNum = 8;
                
            }else if($distance < 140){
                $tarNum = 9;
                
            }else if($distance < 160){
                $tarNum = 10;
                
            }else if($distance < 200){
                $tarNum = 11;
                
            }else if($distance < 300){
                $tarNum = 12;
                
            }else if($distance <500){
                $tarNum = 13;
                
            }else {
                $tarNum = 13;
            }

            $sql = "select tar_prix from vik_tarif where tar_num = :tarNum";
            $stmt = $this->database->prepareStatement($sql);
            $stmt->execute(['tarNum' => $tarNum]);
            $result1 = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "select typ_reduc from vik_type_client where typ_num = (select typ_num from vik_client where cli_num = :cliNum)";
            $stmt = $this->database->prepareStatement($sql);
            $stmt->execute(['cliNum' => $cliNum]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $prix = $result1['TAR_PRIX'] * $result['TYP_REDUC'] / 100;

            $sqlInsert = "INSERT INTO VIK_RESERVATION (CLI_NUM, res_num, tar_num_tranche,res_date, res_nb_points, res_prix_tot) 
                        VALUES (:cliNum, (select max(res_num)+1 as res from vik_reservation
                        where cli_num=:cliNum), :tarNum, SYSDATE, :points, :prix)";
            $stmtInsert = $this->database->prepareStatement($sqlInsert);
            $stmtInsert->execute([
                'cliNum' => $cliNum,
                'tarNum' => $tarNum,
                'villeDepart' => $reservation['ville_depart'],
                'villeArrivee' => $villeArrivee,
                'distance' => $distance,
                'duree' => $minutes,
                'points' => floor($distance) / 10,
                'prix' => $prix
            ]);
        } else {
            $cliNum = 0;
            while ($villeDepart !== $villeArrivee) {
                $sql = "select noe_distance_prochain, noe_duree_prochain, com_code_insee_suivant from vik_noeud where com_code_insee_arret = :villeDepart and lig_num = :ligNum";
                $stmt = $this->database->prepareStatement($sql);
                $stmt->execute(['villeDepart' => $villeDepart, 'ligNum' => $reservation['ligne']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $distance += $result['ETA_DISTANCE'];
                    $minutes += $result['ETA_DUREE_PROCHAIN'];
                    $villeDepart = $result['COM_CODE_INSEE_SUIVANT'];
                }
            }
            if ($distance < 10){
                $tarNum = 1;
            }else if($distance < 20){
                $tarNum = 2;

            }else if($distance < 30){
                $tarNum = 3;
                
            }else if($distance < 40){
                $tarNum = 4;
                
            }else if( $distance < 50){
                $tarNum = 5;
                
            }else if($distance < 60){
                $tarNum = 6;
                
            }else if($distance < 80){
                $tarNum = 7;
                
            }else if($distance < 100){
                $tarNum = 8;
                
            }else if($distance < 140){
                $tarNum = 9;
                
            }else if($distance < 160){
                $tarNum = 10;
                
            }else if($distance < 200){
                $tarNum = 11;
                
            }else if($distance < 300){
                $tarNum = 12;
                
            }else if($distance <500){
                $tarNum = 13;
                
            }else {
                $tarNum = 13;
            }

            $sql = "select tar_prix from vik_tarif where tar_num = :tarNum";
            $stmt = $this->database->prepareStatement($sql);
            $stmt->execute(['tarNum' => $tarNum]);
            $result1 = $stmt->fetch(PDO::FETCH_ASSOC);


            $prix = $result1['TAR_PRIX'];

            $sqlInsert = "INSERT INTO VIK_RESERVATION (CLI_NUM, res_num, tar_num_tranche,res_date, res_nb_points, res_prix_tot) 
                        VALUES (:cliNum, (select max(res_num)+1 as res from vik_reservation
                        where cli_num=:cliNum), :tarNum, SYSDATE, :points, :prix)";
            $stmtInsert = $this->database->prepareStatement($sqlInsert);
            $stmtInsert->execute([
                'cliNum' => 0,
                'tarNum' => $tarNum,
                'villeDepart' => $reservation['ville_depart'],
                'villeArrivee' => $villeArrivee,
                'distance' => $distance,
                'duree' => $minutes,
                'points' => floor($distance) / 10,
                'prix' => $prix
            ]);
        }
    }
}
