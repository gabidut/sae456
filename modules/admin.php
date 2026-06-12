<?php

class Adminitration
{
    private $database;

    private $password_secret;
    private $session_helper;
    /**
     * Summary of __construct
     * @param Database $database
     * @param string $password_secret
     * @param SessionHelper $session_helper
     */
    public function __construct($database, $password_secret, $session_helper)
    {
        $this->database = $database;
        $this->password_secret = $password_secret;
        $this->session_helper = $session_helper;
    }

    public function deleteClient($cliNum)
    {
        $sql = "DELETE FROM vik_client WHERE cli_num = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum]);
    }

    public function listClients(): array
    {
        $sql = "SELECT * FROM vik_client order by cli_num asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsCourriel(): array
    {
        $sql = "SELECT * FROM vik_client order by cli_courriel asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsRang(): array
    {
        $sql = "SELECT * FROM vik_client order by typ_num asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsPrenom(): array
    {
        $sql = "SELECT * FROM vik_client order by cli_prenom asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsNom(): array
    {
        $sql = "SELECT * FROM vik_client order by cli_nom asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsVille(): array
    {
        $sql = "SELECT * FROM vik_client order by cli_ville asc";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortID($cliNum): array
    {
        $sql = "SELECT * FROM vik_client where cli_num LIKE :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum.'%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortCourriel($cli_courriel): array
    {
        $sql = "SELECT * FROM vik_client where lower(cli_courriel) like lower(:cli_courriel )";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_courriel' =>'%' . $cli_courriel.'%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortRang($typ_nom): array
    {
        $sql = "SELECT * FROM vik_client join vik_type_client using (typ_num) where lower(typ_nom) like lower(:typ_nom )";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['typ_nom' => $typ_nom.'%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortPrenom($cli_prenom): array
    {
        $sql = "SELECT * FROM vik_client where lower(cli_prenom) like lower(:cli_prenom ) ";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_prenom' => $cli_prenom. '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortNom($cli_nom): array
    {
        $sql = "SELECT * FROM vik_client where lower(cli_nom) like lower(:cli_nom )";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_nom' => $cli_nom.'%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortVille($cli_ville): array
    {
        $sql = "SELECT * FROM vik_client where lower(cli_ville) like lower(:cli_ville )";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_ville' => $cli_ville . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsInactifs(): array
    {
        $sql = "SELECT * FROM vik_client where cli_date_connec < sysdate - (365*2)";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNbClientConnecteEntre($datedebut, $datefin): array
    {
        $sql = "SELECT count(*) AS total_clients
            FROM vik_client
            WHERE TRUNC(cli_date_connec) BETWEEN TO_DATE(:datedebut, 'DD/MM/YY') AND TO_DATE(:datefin, 'DD/MM/YY')";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['datedebut' => $datedebut, 'datefin' => $datefin]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    public function lignesLesPlusUtilisees($datedebut, $datefin): array
    {
        $sql = "SELECT 
        e.LIG_NUM, 
        COUNT(*) AS TOTAL_UTILISATIONS
        FROM VIK_ETAPE e
        JOIN VIK_RESERVATION r 
        ON e.CLI_NUM = r.CLI_NUM AND e.RES_NUM = r.RES_NUM
        WHERE r.RES_DATE >= TO_DATE(:datedebut, 'DD/MM/YYYY')
        AND r.RES_DATE < TO_DATE(:datefin, 'DD/MM/YYYY') + 1
        GROUP BY e.LIG_NUM
        ORDER BY e.LIG_NUM";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['datedebut' => $datedebut, 'datefin' => $datefin]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;

        foreach ($result as $ligne) {
            $total += $ligne['TOTAL_UTILISATIONS'];
        }

        if ($total > 0) {
            foreach ($result as $index => $ligne) {
                $pourcentage = ($ligne['TOTAL_UTILISATIONS'] / $total) * 100;

                $result[$index]['POURCENTAGE'] = round($pourcentage, 2);
            }
        }

        return ['usages' => $result, 'total' => $total];
    }

    public function top10BestUsers(): array
    {
        $sql = "SELECT cli_num, tot FROM (
                    SELECT cli_num, COUNT(*) as tot 
                    FROM vik_reservation
                    GROUP BY cli_num 
                    ORDER BY tot DESC
                    FETCH FIRST 11 ROWS ONLY
                ) WHERE cli_num != 0";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListeResEntre($datedebut, $datefin): array
    {
        $sql = "SELECT count(*) AS total_res
            FROM vik_reservation
            WHERE TRUNC(res_date    ) BETWEEN TO_DATE(:datedebut, 'DD/MM/YY') AND TO_DATE(:datefin, 'DD/MM/YY')";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['datedebut' => $datedebut, 'datefin' => $datefin]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function updateLigne($lig_num, $newcomdeb, $newcomend)
    {
        $sql = "UPDATE VIK_LIGNE 
                SET COM_CODE_INSEE_DEBU = :newcomdeb, 
                    COM_CODE_INSEE_TERM = :newcomend 
                WHERE LIG_NUM = :lig_num";

        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute([
            'lig_num'   => $lig_num,
            'newcomdeb' => $newcomdeb,
            'newcomend' => $newcomend
        ]);
    }

    public function updateHoraire($lig_num, $code_insee_arret, $nouvelle_heure)
    {
        $sql = "UPDATE VIK_NOEUD 
                SET NOE_HEURE_PASSAGE = :nouvelle_heure 
                WHERE LIG_NUM = :lig_num 
                  AND COM_CODE_INSEE_ARRET = :code_arret";

        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute([
            'nouvelle_heure' => $nouvelle_heure,
            'lig_num'        => $lig_num,
            'code_arret'     => $code_insee_arret
        ]);
    }

    public function insertLigne($lig_num, $comdeb, $comend)
    {
        $sql = "INSERT INTO VIK_LIGNE (LIG_NUM, COM_CODE_INSEE_DEBU, COM_CODE_INSEE_TERM) 
                VALUES (:lig_num, :comdeb, :comend)";

        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute([
            'lig_num' => $lig_num,
            'comdeb'  => $comdeb,
            'comend'  => $comend
        ]);
    }

    public function insertNoeud($lig_num, $code_arret, $code_suivant, $heure_passage, $distance, $duree)
    {
        $sql = "INSERT INTO VIK_NOEUD (LIG_NUM, COM_CODE_INSEE_ARRET, COM_CODE_INSEE_SUIVANT, NOE_HEURE_PASSAGE, NOE_DISTANCE_PROCHAIN, NOE_DUREE_PROCHAIN) 
                VALUES (:lig_num, :code_arret, :code_suivant, TO_DATE(:heure_passage, 'HH24:MI'), :distance, :duree)";

        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute([
            'lig_num'       => $lig_num,
            'code_arret'    => $code_arret,
            'code_suivant'  => $code_suivant,
            'heure_passage' => $heure_passage,
            'distance'      => $distance,
            'duree'         => $duree
        ]);
    }

    public function getToutesLesCommunes(): array
    {
        $sql = "SELECT COM_CODE_INSEE, COM_NOM FROM VIK_COMMUNE ORDER BY COM_NOM ASC";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNoeudsParLigne($lig_num): array
    {
        $sql = "SELECT 
                    n.COM_CODE_INSEE_ARRET AS CODE_ARRET, 
                    c.COM_NOM AS VILLE_ARRET, 
                    n.COM_CODE_INSEE_SUIVANT AS CODE_SUIVANTT, 
                    cs.COM_NOM AS VILLE_SUIVANTTE, 
                    TO_CHAR(n.NOE_HEURE_PASSAGE, 'HH24:MI') AS HEURE_PASSAGE, 
                    n.NOE_DISTANCE_PROCHAIN AS DISTANCE, 
                    n.NOE_DUREE_PROCHAIN AS DUREE 
                FROM VIK_NOEUD n
                JOIN VIK_COMMUNE c ON n.COM_CODE_INSEE_ARRET = c.COM_CODE_INSEE
                LEFT JOIN VIK_COMMUNE cs ON n.COM_CODE_INSEE_SUIVANT = cs.COM_CODE_INSEE
                WHERE n.LIG_NUM = :lig_num
                ORDER BY n.NOE_HEURE_PASSAGE ASC";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['lig_num' => $lig_num]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clientInactif($clientID): bool {
        $sql = "SELECT 1 FROM vik_client WHERE cli_num = :cli_num AND cli_date_connec < sysdate - (365*2)";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(["cli_num" => $clientID]);
        
        return $stmt->fetchColumn() !== false; 
    }

    public function getClientInfoFromId($cliNum)
    {
        $sql = "SELECT cli_num,cli_nom,cli_prenom,cli_courriel,cli_ville,cli_nb_points_ec,cli_nb_points_tot, typ_nom FROM vik_client  join vik_type_client USING (typ_num) WHERE cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['num' => $cliNum]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
