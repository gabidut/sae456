<?php

class Authentificator
{
    private $database;
    private $password_secret;
    /**
     * Summary of __construct
     * @param Database $database
     * @param string $password_secret
     */
    public function __construct($database, $password_secret)
    {
        $this->database = $database;
        $this->password_secret = $password_secret;
    }

    public function isUserLoginValid($email, $password): bool
    {
        $user = $this->database->getClientFromMail($email);
        if (empty($user)) {
            return false;
        } else {
            if ($this->hash_password($password) === $user->cli_mdp) {
                return true;
            }
        }

        return false;
    }
    public function logout() {}
    public function isLoggedIn() {}
    public function hash_password($password)
    {
        return password_hash($this->password_secret . $password, PASSWORD_DEFAULT);
    }
}
