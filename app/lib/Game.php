<?php

require_once(__DIR__.'/../../vendor/autoload.php');
use WebSocket\Client;

Class Game
{

    public $db;
    private $teamid;
    private $team;
    private $leadcat;
    private $leadpoints;
    private $end;
    private $leaderboard;
    private $yellowshirt;
    private $lastleadreset;
    private $_hints;

    public function __construct(&$db)
    {
        $this->db = $db;
        $this->teamid = isset($_SESSION['teamid']) ? $_SESSION['teamid'] : null;
        $this->team = isset($_SESSION['team']) ? $_SESSION['team'] : null;

        $this->reloadGameInfo();
    }

    public function leadCat()
    {
        return $this->leadcat;
    }

    public function leadPoints()
    {
        return $this->leadpoints;
    }

    public function endTS()
    {
        return $this->end;
    }

    public function isSelectLeadMode()
    {
        return ($this->teamid == $this->yellowshirt && empty($this->leadcat) && empty($this->leadpoints));
    }

    public function leaderBoard()
    {
        $this->leaderboard = (is_array($this->leaderboard) ? $this->leaderboard : $this->db->getLeaderBoard($this->teamid));
        return $this->leaderboard;
    }

    public function dashleaderBoard()
    {
         $this->dashleaderboard = (is_array($this->leaderboard) ? $this->leaderboard : $this->db->getLeaderBoard($this->yellowshirt));
         return $this->dashleaderboard;
    }

    // Game Dashboard
    public function json($board)
    {
        $secs2end = $this->endTS() - time();
        return json_encode(array(
            'board' => $board,
            'leaderboard' => $this->leaderBoard(),
            'time' => $secs2end,
            'hints' => $this->_hints
        ));
    }

    // Public Dashboard
    public function dashjson($board)
    {
        $secs2end = $this->endTS() - time();
        return json_encode(array(
            'board' => $board,
            'leaderboard' => $this->dashleaderBoard(),
            'time' => $secs2end,
            'hints' => $this->_hints
        ));
    }

    public function electNewLeadQuestion()
    {
        // check lowest point questions and rand
        // getOpenQuestions(), new array w/1st element cat=>points, sort array by value
    }

    public function resetLeadQuestion($postcat, $postquestion)
    {
        $response = $this->db->resetLeadQuestion($this->teamid, $postcat, $postquestion);
        $this->forceClientRefresh();
        return $response;
    }

    public function setNewLeadQuestion($leadcat, $leadquestion)
    {
        $response = $this->db->setNewLeadQuestion($this->teamid, $leadcat, $leadquestion);
        $this->forceClientRefresh();
        return $response;
    }

    public function reloadGameInfo()
    {
        $gameinfo = $this->db->getGameInfo();
        $this->leadcat = $gameinfo['leadcat'];
        $this->leadpoints = $gameinfo['leadpoints'];
        $this->end = $gameinfo['end'];
        $this->yellowshirt = $gameinfo['yellowshirt'];
        $this->lastleadreset = $gameinfo['lastleadreset'];
        $this->_hints = $gameinfo['hints'];
    }

    public function getHints()
    {
        return $this->_hints;
    }

    public function dashboard($questions)
    {
        $board = array();
        $stat = $this->db->getDashboard($this->leadCat(), $this->leadPoints());
        foreach ($questions as $cat => $q) {
            foreach ($q as $points => $qa) {
                $board[$cat][$points]['class'] = (isset($stat[$cat][$points]['class'])) ? $stat[$cat][$points]['class'] : 'closed';
                $board[$cat][$points]['teams'] = (isset($stat[$cat][$points]['teams'])) ? $stat[$cat][$points]['teams'] : '';
            }
        }
        return $board;
    }

    public function closedQuestions()
    {
        $q = $this->db->getClosedQuestions();
        return $q;
    }

    // Force clients to reload
    public function forceClientRefresh()
    {
        global $conf;

        try {
            // Connect to websocket
            $client = new Client($conf['websocket']);
            // Send empty message
            $client->send(json_encode(array($conf['websocket-key'] => NULL)));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
