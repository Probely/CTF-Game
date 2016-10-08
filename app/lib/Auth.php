<?php

Class Auth
{

    private $db;
    private $team;
    private $teamid;
    private $authip;

    public function __construct(&$db)
    {
        $this->db = $db;
        if (isset($_SESSION['teamid']) && isset($_SESSION['team'])) {
            $this->teamid = $_SESSION['teamid'];
            $this->team = $_SESSION['team'];
            $this->authip = $_SESSION['authip'];
        }
    }

    public function login($key)
    {
        $team = $this->db->getAuth($key);
        if (!$team) {
            return false;
        }
        $this->team = $team['team'];
        $this->teamid = $team['id'];
        $this->authip = $_SERVER['REMOTE_ADDR'];
        $_SESSION['authip'] = $this->authip;
        $_SESSION['teamid'] = $this->teamid;
        $_SESSION['team'] = $this->team;
        return true;
    }

    public static function check()
    {
        //echo "<br><br><br><pre>";print_r($_SESSION);print_r($_COOKIE);
        return (isset($_SESSION['teamid']) && isset($_SESSION['team']) &&
                    isset($_SESSION['authip']) && $_SESSION['authip'] == $_SERVER['REMOTE_ADDR']);
    }

    public static function ip_check()
    {
        global $conf;

        $ip='';
        // Use cloudflare ip first (XXX: WARNING This should be removed if not using cloudflare!)
        if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else { // Otherwise, remote addr
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        foreach($conf['whitelist'] as $ipregexp) {
            if(preg_match($ipregexp, $ip) == 1) {
                return true;
            }
        }

        return false;
    }

    public function getTeamId()
    {
        return $this->teamid;
    }

    public function getTeam()
    {
        return $this->team;
    }
}
