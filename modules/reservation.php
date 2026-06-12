<?php

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
        $sql = "SELECT DISTINCT COM_NOM, COM_CODE_INSEE, COM_LAT, COM_LONG FROM vik_commune ORDER BY COM_NOM ASC";
        $stmt = $this->database->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listCitiesAndTheirSteps(): array
    {
        $sql = "SELECT com1.com_code_insee as depart, com2.com_code_insee as arrivee, lig_num as ligne
        from vik_noeud noe
        join vik_commune com1 on noe.com_code_insee_arret=com1.com_code_insee
        join vik_commune com2 on noe.com_code_insee_suivant=com2.com_code_insee
        group by com1.com_code_insee, com2.com_code_insee, noe_heure_passage, lig_num
        order by min(noe_heure_passage)";
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

    private function getInseeCode($nomVille)
    {
        $sql = "SELECT COM_CODE_INSEE FROM VIK_COMMUNE WHERE UPPER(COM_NOM) = UPPER(:nom)";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['nom' => $nomVille]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['COM_CODE_INSEE'] : null;
    }

    public function createReservation($reservationArray)
    {
        $distanceTotal = 0;
        $etapes = [];

        $cliNum = 0;
        if ($this->sessionHelper->isUserLoggedIn()) {
            $client = $this->sessionHelper->getUserSession();
            $cliNum = $client['CLI_NUM'];
        }

        foreach ($reservationArray as $segment) {
            $ligNum = $segment['ligne'];

            $villeDepartCode = $this->getInseeCode($segment['depart']);
            $villeArriveeCode = $this->getInseeCode($segment['arrivee']);


            if (!$villeDepartCode || !$villeArriveeCode) continue;

            $courant = $villeDepartCode;
            $safeguard = 0;

            while ($courant !== $villeArriveeCode && $safeguard < 50) {
                $sql = "SELECT NOE_DISTANCE_PROCHAIN, COM_CODE_INSEE_SUIVANT, 
                        TO_CHAR(NOE_HEURE_PASSAGE, 'YYYY-MM-DD HH24:MI:SS') AS NOE_HEURE_FMT
                        FROM vik_noeud 
                        WHERE com_code_insee_arret = :courant 
                        AND lig_num = :ligNum
                        AND ROWNUM = 1";

                $stmt = $this->database->prepareStatement($sql);


                $stmt->execute(['courant' => $courant, 'ligNum' => $ligNum]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $distEtape = isset($result['NOE_DISTANCE_PROCHAIN']) ? (float) str_replace(',', '.', $result['NOE_DISTANCE_PROCHAIN']) : 0;

                    $etapes[] = [
                        'ligne'   => $ligNum,
                        'depart'  => $courant,
                        'arrivee' => $result['COM_CODE_INSEE_SUIVANT'],
                        'dist'    => $distEtape,
                        'heure'   => $result['NOE_HEURE_FMT']
                    ];

                    $distanceTotal += $distEtape;
                    $courant = $result['COM_CODE_INSEE_SUIVANT'];
                } else {
                    break;
                }
                $safeguard++;
            }
        }

        if (empty($etapes)) return;

        if ($cliNum !== 0) {
            $this->authentificator->ajoutPointApresResa($cliNum, $distanceTotal);
        }



        $tarNum = 13;
        if ($distanceTotal < 10) {
            $tarNum = 1;
        } else if ($distanceTotal < 20) {
            $tarNum = 2;
        } else if ($distanceTotal < 30) {
            $tarNum = 3;
        } else if ($distanceTotal < 40) {
            $tarNum = 4;
        } else if ($distanceTotal < 50) {
            $tarNum = 5;
        } else if ($distanceTotal < 60) {
            $tarNum = 6;
        } else if ($distanceTotal < 80) {
            $tarNum = 7;
        } else if ($distanceTotal < 100) {
            $tarNum = 8;
        } else if ($distanceTotal < 140) {
            $tarNum = 9;
        } else if ($distanceTotal < 160) {
            $tarNum = 10;
        } else if ($distanceTotal < 200) {
            $tarNum = 11;
        } else if ($distanceTotal < 300) {
            $tarNum = 12;
        }


        $sql = "SELECT TAR_PRIX FROM vik_tarif WHERE TAR_NUM_TRANCHE = :tarNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['tarNum' => $tarNum]);
        $result1 = $stmt->fetch(PDO::FETCH_ASSOC);

        $prix = $result1['TAR_PRIX'];


        if ($cliNum !== 0) {
            $sql = "SELECT TYP_REDUC FROM vik_type_client WHERE typ_num = (SELECT typ_num FROM vik_client WHERE cli_num = :cliNum)";
            $stmt = $this->database->prepareStatement($sql);
            $stmt->execute(['cliNum' => $cliNum]);
            $resultClient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultClient) {
                $prix = $prix * $resultClient['TYP_REDUC'] / 100;
            }
        }



        $points = floor($distanceTotal) / 10;

        $sqlRes = "SELECT NVL(MAX(RES_NUM), 0) + 1 AS NEW_RES FROM vik_reservation WHERE CLI_NUM = :cliNum";
        $stmtRes = $this->database->prepareStatement($sqlRes);
        $stmtRes->execute(['cliNum' => $cliNum]);
        $rowRes = $stmtRes->fetch(PDO::FETCH_ASSOC);
        $newResNum = $rowRes['NEW_RES'];

        $sqlInsert = "INSERT INTO VIK_RESERVATION (CLI_NUM, RES_NUM, TAR_NUM_TRANCHE, RES_DATE, RES_NB_POINTS, RES_PRIX_TOT) 
                      VALUES (:cliNum, :resNum, :tarNum, SYSDATE, :points, :prix)";
        $stmtInsert = $this->database->prepareStatement($sqlInsert);
        $stmtInsert->execute([
            'cliNum' => $cliNum,
            'resNum' => intval($newResNum),
            'tarNum' => $tarNum,
            'points' => intval(floor($points)),
            'prix'   => intval($prix)
        ]);

        $sqlInsertEtape = "INSERT INTO VIK_ETAPE (LIG_NUM, CLI_NUM, RES_NUM, COM_CODE_INSEE_DEPART, COM_CODE_INSEE_ARRIVEE, ETA_DISTANCE, ETA_HEURE) 
                           VALUES (:ligNum, :cliNum, :resNum, :dep, :arr, :dist, TO_DATE(:heure, 'YYYY-MM-DD HH24:MI:SS'))";
        $stmtEtape = $this->database->prepareStatement($sqlInsertEtape);
        foreach ($etapes as $etape) {
            $stmtEtape->execute([
                'ligNum' => $etape['ligne'],
                'cliNum' => intval($cliNum),
                'resNum' => intval($newResNum),
                'dep'    => $etape['depart'],
                'arr'    => $etape['arrivee'],
                'dist'   => str_replace('.', ',', (string)$etape['dist']),
                'heure'  => $etape['heure']
            ]);
        }

        return [
            'reservation_id' => $newResNum,
            'prix' => $prix,
            'points' => intval(floor($points)),
            'distance' => $distanceTotal,
            'etapes' => $etapes,
            'cliNum' => $cliNum
        ];
    }

    public function getFinalHoraire($lineId, $codeInseeDepart, $codeInseeArrivee, $horaireDepart)
    {
        $duree = 0;
        $codeInseeDepart = $this->getInseeCode($codeInseeDepart);
        $codeInseeArrivee = $this->getInseeCode($codeInseeArrivee);
        $sql = "SELECT NOE_DUREE_PROCHAIN, COM_CODE_INSEE_SUIVANT 
                FROM VIK_NOEUD 
                WHERE COM_CODE_INSEE_ARRET = :depart 
                AND lig_num = :ligne";
        $stmt = $this->database->prepareStatement($sql);

        $currentDepart = $codeInseeDepart;

        while ($currentDepart !== $codeInseeArrivee) {
            $stmt->execute([
                'ligne' => $lineId,
                'depart' => $currentDepart
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new \Exception("Chemin introuvable ou rupture de la ligne entre $codeInseeDepart et $codeInseeArrivee.");
            }

            $duree += (int) $row['NOE_DUREE_PROCHAIN'];
            $currentDepart = $row['COM_CODE_INSEE_SUIVANT'];
        }

        $datePrevue = new \DateTime($horaireDepart);

        $datePrevue->modify("+$duree minutes");
        return ['horaires' => [$datePrevue->format('H:i')]];
    }

    public function simulatePrice($reservationArray)
    {
        $distanceTotal = 0;
        $cliNum = 0;

        if ($this->sessionHelper->isUserLoggedIn()) {
            $client = $this->sessionHelper->getUserSession();
            $cliNum = $client['CLI_NUM'];
        }

        foreach ($reservationArray as $segment) {
            $ligNum = $segment['ligne'];
            $villeDepartCode = $this->getInseeCode($segment['depart']);
            $villeArriveeCode = $this->getInseeCode($segment['arrivee']);

            if (!$villeDepartCode || !$villeArriveeCode) continue;

            $courant = $villeDepartCode;
            $safeguard = 0;

            while ($courant !== $villeArriveeCode && $safeguard < 50) {
                $sql = "SELECT NOE_DISTANCE_PROCHAIN, COM_CODE_INSEE_SUIVANT 
                        FROM vik_noeud 
                        WHERE com_code_insee_arret = :courant 
                        AND lig_num = :ligNum AND ROWNUM = 1";
                $stmt = $this->database->prepareStatement($sql);
                $stmt->execute(['courant' => $courant, 'ligNum' => $ligNum]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $distEtape = isset($result['NOE_DISTANCE_PROCHAIN']) ? (float) str_replace(',', '.', $result['NOE_DISTANCE_PROCHAIN']) : 0;
                    $distanceTotal += $distEtape;
                    $courant = $result['COM_CODE_INSEE_SUIVANT'];
                } else {
                    break;
                }
                $safeguard++;
            }
        }

        if ($distanceTotal == 0) return ['prix' => 0];

        $tarNum = 13;
        if ($distanceTotal < 10) $tarNum = 1;
        else if ($distanceTotal < 20) $tarNum = 2;
        else if ($distanceTotal < 30) $tarNum = 3;
        else if ($distanceTotal < 40) $tarNum = 4;
        else if ($distanceTotal < 50) $tarNum = 5;
        else if ($distanceTotal < 60) $tarNum = 6;
        else if ($distanceTotal < 80) $tarNum = 7;
        else if ($distanceTotal < 100) $tarNum = 8;
        else if ($distanceTotal < 140) $tarNum = 9;
        else if ($distanceTotal < 160) $tarNum = 10;
        else if ($distanceTotal < 200) $tarNum = 11;
        else if ($distanceTotal < 300) $tarNum = 12;

        $sql = "SELECT TAR_PRIX FROM vik_tarif WHERE TAR_NUM_TRANCHE = :tarNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['tarNum' => $tarNum]);
        $result1 = $stmt->fetch(PDO::FETCH_ASSOC);
        $prix = (float) $result1['TAR_PRIX'];

        if ($cliNum !== 0) {
            $sql = "SELECT TYP_REDUC FROM vik_type_client WHERE typ_num = (SELECT typ_num FROM vik_client WHERE cli_num = :cliNum)";
            $stmt = $this->database->prepareStatement($sql);
            $stmt->execute(['cliNum' => $cliNum]);
            $resultClient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultClient) {
                $prix = $prix * (1 - ($resultClient['TYP_REDUC'] / 100));
            }
        }

        return ['prix' => number_format($prix, 2, '.', '')];
    }

    public function usePoints($cliNum, $pointsToUse):int
    {
        $sql = "UPDATE vik_client SET CLI_nb_POINTS_ec = CLI_nb_POINTS_ec - :pointsToUse WHERE CLI_NUM = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['pointsToUse' => $pointsToUse, 'cliNum' => $cliNum]);
        $reduc = 0;
        if($pointsToUse == 100) {
            $reduc = 1;
        } else if ($pointsToUse == 500) {
            $reduc = 7;
        }else if ($pointsToUse == 1000) {
            $reduc = 15;
        }
        return $reduc;
    }

    public function usablePoints100($cliNum):bool
    {
        $sql = "SELECT CLI_nb_POINTS_ec FROM vik_client WHERE CLI_NUM = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && (int) $result['CLI_nb_POINTS_ec'] >= 100;
    }

    public function usablePoints500($cliNum):bool
    {
        $sql = "SELECT CLI_nb_POINTS_ec FROM vik_client WHERE CLI_NUM = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && (int) $result['CLI_nb_POINTS_ec'] >= 500;
    }

    public function usablePoints1000($cliNum):bool
    {
        $sql = "SELECT CLI_nb_POINTS_ec FROM vik_client WHERE CLI_NUM = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && (int) $result['CLI_nb_POINTS_ec'] >= 1000;
    }
}
