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

    public function listClientsNum(): array
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
        $sql = "SELECT * FROM vik_client where cli_num = :cliNum";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cliNum' => $cliNum]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortCourriel($cli_courriel): array
    {
        $sql = "SELECT * FROM vik_client where cli_courriel = :cli_courriel";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_courriel' => $cli_courriel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortRang($typ_num): array
    {
        $sql = "SELECT * FROM vik_client where typ_num = :typ_num";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['typ_num' => $typ_num]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortPrenom($cli_prenom): array
    {
        $sql = "SELECT * FROM vik_client where cli_prenom = :cli_prenom";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_prenom' => $cli_prenom]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortNom($cli_nom): array
    {
        $sql = "SELECT * FROM vik_client where cli_nom = :cli_nom";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_nom' => $cli_nom]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listClientsSortVille($cli_ville): array
    {
        $sql = "SELECT * FROM vik_client where cli_ville = :cli_ville";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['cli_ville' => $cli_ville]);
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


}
