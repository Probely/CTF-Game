<?php

Class Team
{

    private $db;
    private $game;
    private $teamid;
    private $team;
    private $points;

    public function __construct(&$game)
    {
        $this->game = $game;
        $this->db = $this->game->db;
        $this->teamid = $_SESSION['teamid'];
        $this->team = $_SESSION['team'];
    }

    public function name()
    {
        return $this->team;
    }

    public function id()
    {
        return $this->teamid;
    }
    public function points()
    {
        return (isset($this->points) ? $this->points : $this->db->getTeamPoints($this->teamid));
    }

    public function board($questions)
    {
        $board = array();
        $stat = $this->db->getTeamStatusQuestions($this->teamid, $this->game->leadCat(), $this->game->leadPoints());
        foreach ($questions as $cat => $q) {
            foreach ($q as $points => $qa) {
                $board[$cat][$points] = (isset($stat[$cat][$points])) ? $stat[$cat][$points] : '';
            }
        }
        return $board;
    }

    public function boardSelectLead($questions)
    {
        $board = array();
        foreach ($questions as $cat => $q) {
            foreach ($q as $points => $qa) {
                if (empty($questions[$cat][$points])) {
                    $board[$cat][$points] = 'select_lead';
                } else if ($questions[$cat][$points] == 'open') {
                    $board[$cat][$points] = 'openstatic';
                } else {
                    $board[$cat][$points] = $questions[$cat][$points];
                }
            }
        }
        return $board;
    }

    public function answer($cat, $question)
    {
        return $this->db->insertAnswer($this->teamid, $cat, $question);
    }

    public function ip()
    {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress .= ",".$_SERVER['HTTP_CLIENT_IP'];
        } else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress .= ",".$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress .= ",".$_SERVER['HTTP_X_FORWARDED'];
        } else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress .= ",".$_SERVER['HTTP_FORWARDED_FOR'];
        } else if(isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress .= ",".$_SERVER['HTTP_FORWARDED'];
        } else if(isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress .= ",".$_SERVER['REMOTE_ADDR'];
        }

        return $ipaddress;
    }


}
