<?php

class Authentificator
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

    public function processAuth($email, $password): array
    {
        $user = $this->getClientFromMail($email);
        var_dump($user);
        if (empty($user)) {
            throw new AuthExeption("Invalid email or password 1");
        } else {
            if ($this->verify_password($password, $user['CLI_MDP'])) {
                $this->session_helper->setUserSession($user['CLI_NUM']);
                $this->updateConnexionDate($user['CLI_NUM']);
                return $user;
            }
        }

        throw new AuthExeption("Invalid email or password 2");
    }
    public function logout()
    {
        $this->session_helper->clearUserSession();
    }
    public function isLoggedIn()
    {
        return $this->session_helper->isUserLoggedIn();
    }
    public function hash_password($password)
    {
        return password_hash($this->password_secret . $password, PASSWORD_DEFAULT);
    }

    public function verify_password($password, $hash)
    {
        return password_verify($this->password_secret . $password, $hash);
    }

    public function getClientFromMail($email): array
    {
        $sql = 'SELECT * FROM vik_client WHERE CLI_COURRIEL = :email';
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            return $result[0];
        }
        return [];
    }
    /**
     * @param string $dep
     * @param string $ville
     * @param string $nom
     * @param string $prenom
     * @param string $mdp
     * @param string $mail
     * @param string $tel
     * @return bool
     */
    public function insertUser($dep, $ville, $nom, $prenom, $mdp, $mail, $tel)
    {
        $sql = "insert into vik_client(TYP_NUM,DEP_NUM,CLI_NOM,CLI_PRENOM,CLI_VILLE,CLI_TELEPHONE,CLI_COURRIEL,cli_nb_points_ec,cli_nb_points_tot,cli_date_connec, cli_mdp) values ('10',:dep,upper(:nom),initcap(:prenom),:ville,:tel,:mail,'0','0',sysdate,:mdp)";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['dep' => $dep, 'ville' => $ville, 'nom' => $nom, 'prenom' => $prenom, 'mdp' => $mdp, 'mail' => $mail, 'tel' => $tel]);
    }

    public function updateConnexionDate($num_utilisateur)
    {
        $sql = "update vik_client set cli_date_connec = sysdate where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur]);
    }

    /**
     * @param mixed $num_utilisateur
     * @param string $newmdp PASSWORD NOT HASHED
     * @return bool
     */
    public function changePassword($num_utilisateur, $newmdp)
    {
        $sql = "update vik_client set cli_mdp = :newmdp where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newmdp' => $this->hash_password($newmdp)]);
    }

    public function changeMail($num_utilisateur, $newmail)
    {
        $sql = "update vik_client set cli_courriel = :newmail where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newmail' => $newmail]);
    }

    public function changeNom($num_utilisateur, $newNom)
    {
        $sql = "update vik_client set cli_nom = upper(:newNom) where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newNom' => $newNom]);
    }

    public function changePrenom($num_utilisateur, $newPrenom)
    {
        $sql = "update vik_client set cli_prenom = initcap(:newPrenom) where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newPrenom' => $newPrenom]);
    }

    public function changeTel($num_utilisateur, $newTel)
    {
        $sql = "update vik_client set cli_telephone = :newTel where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newTel' => $newTel]);
    }
    public function changeVille($num_utilisateur, $newVille)
    {
        $sql = "update vik_client set cli_ville = :newVille where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'newVille' => $newVille]);
    }

    public function updatePointTot($num_utilisateur, $point)
    {
        $sql = "update vik_client set cli_nb_points_tot = :point where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'point' => $point]);
    }

    public function updatePointEC($num_utilisateur, $point)
    {
        $sql = "update vik_client set cli_nb_points_ec = :point where cli_num = :num";
        $stmt = $this->database->prepareStatement($sql);
        return $stmt->execute(['num' => $num_utilisateur, 'point' => $point]);
    }

    public function ajoutPointApresResa($num_utilisateur, $nbkilometre)
    {
        $nbpoints = floor($nbkilometre/10) ;

        $sqlPoints = "UPDATE vik_client SET cli_nb_points_ec = cli_nb_points_ec + :nbpoints, cli_nb_points_tot = cli_nb_points_tot + :nbpoints WHERE cli_num = :num";

        $stmtPoints = $this->database->prepareStatement($sqlPoints);
        $success = $stmtPoints->execute(['num' => $num_utilisateur, 'nbpoints' => $nbpoints]);

        if (!$success) {
            return false;
        }

        $sqlGetTotal = "SELECT cli_nb_points_tot FROM vik_client WHERE cli_num = :num";
        $stmtGetTotal = $this->database->prepareStatement($sqlGetTotal);
        $stmtGetTotal->execute(['num' => $num_utilisateur]);
        $client = $stmtGetTotal->fetch();

        $newTotalPoints = $client['cli_nb_points_tot'];

        $sqlGetTier = "SELECT TYP_NUM FROM vik_type_client WHERE :points <= TYP_PT_LIMITE ORDER BY TYP_PT_LIMITE ASC";
        $stmtGetTier = $this->database->prepareStatement($sqlGetTier);
        $stmtGetTier->execute(['points' => $newTotalPoints]);
        $tier = $stmtGetTier->fetch();

        if ($tier) {
            $newType = $tier['TYP_NUM'];
        } else {
            $newType = 5;
        }
        $sqlUpgrade = "UPDATE vik_client SET typ_num = :newType WHERE cli_num = :num";
        $stmtUpgrade = $this->database->prepareStatement($sqlUpgrade);

        return $stmtUpgrade->execute(['newType' => $newType, 'num' => $num_utilisateur]);
    }

    public function getReservation($numClient): array
    {
        $sql = 'select cli_prenom, res_num, res_date, res_prix_tot, lig_num, 
        a.com_nom , b.com_nom, eta_heure 
        from vik_reservation 
        join vik_client using (cli_num) 
        join vik_etape using (cli_num, res_num)
        join vik_commune a on a.com_code_insee = vik_etape.com_code_insee_depart
        join vik_commune b on b.com_code_insee = vik_etape.com_code_insee_arrivee
        where cli_num = :numClient
        order by res_date';
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['numClient' => $numClient]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            return $result;
        }
        return [];
    }
}

class AuthExeption extends Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
