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
        if (empty($user)) {
            throw new AuthException("Email inconnu."); // Erreur propre
        }
        if (!$this->verify_password($password, $user['CLI_MDP'])) {
            throw new AuthException("Mot de passe incorrect."); // Erreur propre
        }
        return $user;
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
        $sql = "insert into vik_client(TYP_NUM,DEP_NUM,CLI_NOM,CLI_PRENOM,CLI_VILLE,CLI_TELEPHONE,CLI_COURRIEL,cli_nb_points_ec,cli_nb_points_tot,cli_date_connec, cli_mdp) values ('1',:dep,upper(:nom),initcap(:prenom),:ville,:tel,:mail,'10','10',sysdate,:mdp) RETURNING cli_num INTO :new_id";
        $stmt = $this->database->prepareStatement($sql);

        $newId = 0;

        $stmt->bindParam(':new_id', $newId, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->bindParam(':dep', $dep);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':mdp', $mdp);

        $success = $stmt->execute();

        if ($success && $newId) {
            return $newId;
        }

        return -1;
    }

    public function getIsAdmin($userID)
    {
        $sql = 'SELECT CLI_ROLE FROM VIK_CLIENT WHERE CLI_NUM = :userId';
        $stmt = $this->database->prepareStatement($sql);
        $stmt->bindParam(':userId', $userID, PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
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
        $nbpoints = floor($nbkilometre / 10);

        $sqlPoints = "UPDATE vik_client SET cli_nb_points_ec = cli_nb_points_ec + :nbpoints, cli_nb_points_tot = cli_nb_points_tot + :nbpoints WHERE cli_num = :num";

        $stmtPoints = $this->database->prepareStatement($sqlPoints);
        $success = $stmtPoints->execute(['num' => intval($num_utilisateur), 'nbpoints' => intval($nbpoints)]);

        if (!$success) {
            return false;
        }

        $sqlGetTotal = "SELECT cli_nb_points_tot FROM vik_client WHERE cli_num = :num";
        $stmtGetTotal = $this->database->prepareStatement($sqlGetTotal);
        $stmtGetTotal->execute(['num' => intval($num_utilisateur)]);
        $client = $stmtGetTotal->fetch();

        $newTotalPoints = $client['CLI_NB_POINTS_TOT'];

        $sqlGetTier = "SELECT TYP_NUM FROM vik_type_client WHERE :points >= TYP_PT_LIMITE ORDER BY TYP_PT_LIMITE desc fetch first 1 rows only";
        $stmtGetTier = $this->database->prepareStatement($sqlGetTier);
        $stmtGetTier->execute(['points' => intval($newTotalPoints)]);
        $tier = $stmtGetTier->fetch();

        if ($tier) {
            $newType = $tier['TYP_NUM'];
        } else {
            $newType = 1;
        }
        $sqlUpgrade = "UPDATE vik_client SET typ_num = :newType WHERE cli_num = :num";
        $stmtUpgrade = $this->database->prepareStatement($sqlUpgrade);

        return $stmtUpgrade->execute(['newType' => $newType, 'num' => $num_utilisateur]);
    }

    public function getReservation($numClient): array
    {
        $sql = "
            SELECT 
                c.cli_nom, 
                r.res_num, 
                r.res_date, 
                r.res_prix_tot, 
                e_deb.lig_num, 
                c_deb.com_nom AS DEPART, 
                c_fin.com_nom AS ARRIVE, 
                TO_CHAR(e_deb.eta_heure, 'HH24:MI') AS HEURE_DEPART
            FROM vik_reservation r
            -- Remplacement du USING par un ON --
            JOIN vik_client c ON r.cli_num = c.cli_num
            
            JOIN vik_etape e_deb ON r.res_num = e_deb.res_num AND r.cli_num = e_deb.cli_num
            JOIN vik_commune c_deb ON c_deb.com_code_insee = e_deb.com_code_insee_depart
            
            JOIN vik_etape e_fin ON r.res_num = e_fin.res_num AND r.cli_num = e_fin.cli_num
            JOIN vik_commune c_fin ON c_fin.com_code_insee = e_fin.com_code_insee_arrivee
            
            WHERE r.cli_num = :numClient
            AND e_deb.eta_heure = (SELECT MIN(eta_heure) FROM vik_etape WHERE res_num = r.res_num AND cli_num = r.cli_num)
            AND e_fin.eta_heure = (SELECT MAX(eta_heure) FROM vik_etape WHERE res_num = r.res_num AND cli_num = r.cli_num)
            
            ORDER BY r.res_date ASC
        ";

        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['numClient' => $numClient]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return (count($result) > 0) ? $result : [];
    }
}

class AuthException extends Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
