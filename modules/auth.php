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
            throw new AuthExeption("Invalid email or password");
        } else {
            if ($this->verify_password($password, $user['CLI_MDP'])) {
                echo "Password verified";
                $this->session_helper->setUserSession($user['CLI_NUM']);
                $this->updateConnexionDate($user['CLI_NUM']);
                return $user;
            }
        }

        throw new AuthExeption("Invalid email or password");
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
        if ($stmt->rowCount() === 0) {
            return [];
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $sql = "insert into vik_client(TYP_NUM,DEP_NUM,CLI_NOM,CLI_PRENOM,CLI_VILLE,CLI_TELEPHONE,CLI_COURRIEL,cli_nb_points_ec,cli_nb_points_tot,cli_date_connec, cli_mdp) values ('1',:dep,:nom,:prenom,:ville,:tel,:mail,'0','0',sysdate,:mdp)";
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
}

class AuthExeption extends Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
