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
}
