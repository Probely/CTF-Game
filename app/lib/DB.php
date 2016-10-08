<?php

Class DB
{
    private $dbh;

    public function __construct($conf)
    {
        try {
            $this->dbh = new PDO($conf['dsn'], $conf['user'], $conf['pass']);

            // XXX: Hack, the queries using groupby should be revised!
            $this->dbh->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

            // Set timezone
            $this->dbh->query("SET time_zone = '+01:00'");

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit(1);
        }
    }

    // TO DO : validate errors
    public function getAuth($key)
    {
        $query = $this->dbh->prepare("SELECT id,team,tkey FROM teams WHERE tkey= ?");
        $query->execute(array(sha1($key)));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        //var_dump($row);
        return $row;
    }

    // TO DO : validate errors
    public function getTeamPoints($teamid)
    {
        $query = $this->dbh->prepare("SELECT sum(points) AS points FROM answers WHERE teamid= ?");
        $query->execute(array($teamid));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return (isset($row['points']) ? $row['points'] : 0);
    }

    // TO DO : validate errors
    public function getOpenQuestions($leadcat=0, $leadpoints=0)
    {
        $stat = array();

        // Retrieve all answered questions
        $query = $this->dbh->prepare("SELECT cat, question FROM answers GROUP BY cat,question ORDER BY cat,question");
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $stat[$row['cat']][$row['question']] = 'open';
        }
        $query = null;

        // lead question
        if ($leadcat && $leadpoints) {
            $stat[$leadcat][$leadpoints] = 'lead';
        }

        return $stat;
    }

    public function getDashboard($leadcat=0, $leadpoints=0)
    {
        $query = $this->dbh->prepare("SELECT cat, question, teamid FROM answers ORDER BY cat,question,answeredts");
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $stat[$row['cat']][$row['question']]['class'] = 'open';
            $stat[$row['cat']][$row['question']]['teams'][] = $row['teamid'];
        }
        $query = null;

        // lead question
        if ($leadcat && $leadpoints) {
            $stat[$leadcat][$leadpoints]['class'] = 'lead';
        }

        return $stat;
    }

    public function getTeamStatusQuestions($teamid, $leadcat, $leadpoints)
    {
        $stat = $this->getOpenQuestions($leadcat, $leadpoints);

        // Retrieve questions that team answered
        $query = $this->dbh->prepare("SELECT cat, question FROM answers WHERE teamid= ? ORDER BY cat,question");
        $query->execute(array($teamid));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $stat[$row['cat']][$row['question']] = 'answered';
        }
        $query = null;

        return $stat;
    }

    // TO DO : validate errors
    public function getGameInfo()
    {
        // Retrieve lead question
        $query = $this->dbh->prepare(
            "SELECT leadcat, leadpoints, yellowshirt,
                    unix_timestamp(lastleadreset) as lastleadreset,
                    unix_timestamp(end) as end
             FROM game"
        );
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);

        // Retrieve hints (only if the game is running)
        $hints = array();
        if (time() < $row['end']) {
            $hints_query = $this->dbh->prepare("SELECT hint, TIME(timestamp) as timestamp FROM hints ORDER BY id DESC");
            $hints_query->execute();
            $hints = $hints_query->fetchAll(PDO::FETCH_ASSOC);
        }

        return array(
            'leadcat' => $row['leadcat'],
            'leadpoints' => $row['leadpoints'],
            'yellowshirt' => $row['yellowshirt'],
            'lastleadreset' => $row['lastleadreset'],
            'end' => $row['end'],
            'hints' => $hints
        );
    }

    // TO DO : validate errors
    public function getLeaderBoard($teamid)
    {
        // select teams.id, teams.team, sum(answers.points) as points from teams left join answers  on  teams.id=answers.teamid group by teams.id;
        $query = $this->dbh->prepare(
            "SELECT teams.id as teamid,
                    teams.team as team,
                    IFNULL(sum(answers.points),0) as points
            FROM teams LEFT JOIN answers on teams.id=answers.teamid
            GROUP BY teams.id ORDER BY points DESC, answeredts ASC, teamid ASC"
        );
        $query->execute();
        $leaderboard = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $leaderboard[] = array(
                'teamid' => $row['teamid'],
                'team' => $row['team'] . " (Team " . $row['teamid'] . ")",
                'points' => $row['points'],
                'me' => ($row['teamid'] == $teamid)
            );
        }

        $query = null;
        return $leaderboard;
    }

    public function getNumberOfAnswers($cat, $question)
    {
        $query = $this->dbh->prepare("SELECT count(*) AS c FROM answers WHERE cat=? AND question=? AND teamid<>0");
        $query->execute(array($cat, $question));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['c'];
    }

    // TO DO : validate errors
    public function insertAnswer($teamid, $cat, $question)
    {
        $numanswers = $this->getNumberOfAnswers($cat, $question);
        if ($numanswers == 0) {
            $mult = 1;
        } else if ($numanswers == 1) {
            $mult = 0.95;
        } else {
            $mult = 0.9;
        }
        $query = $this->dbh->prepare("INSERT INTO answers (teamid,cat,question,points) values (?, ?, ?, ?)");
        $query->execute(array($teamid, $cat, $question, $mult * $question));
        return true;
    }

    public function electNewLeadQuestion($questions)
    {
        // check lowest point questions and rand
        $gameinfo = $this->getGameInfo();
        if (empty($gameinfo['leadcat']) || empty($gameinfo['leadpoints'])) {
            //return false;
        }

        $openQ = $this->getOpenQuestions();

        $lowq = array();
        foreach ($questions as $cat => $q) {
            foreach ($q as $points => $qa) {
                if (!isset($openQ[$cat][$points])) {
                    if ((isset($lowq[$cat]) && $points < $lowq[$cat]) || !isset($lowq[$cat])) {
                        $lowq[$cat] = $points;
                    }
                }
            }
        }
        // get the categories with the lowest points available
        if (count($lowq)) {
            asort($lowq);
            $lowestpoints = reset($lowq);
            $_q = array_keys($lowq, $lowestpoints);
            shuffle($_q);
            $cat = reset($_q);
            if ($lowestpoints && $cat) {
                // set the new lead question
                $query = $this->dbh->prepare("UPDATE game SET leadcat=?,leadpoints=? ");
                $query->execute(array($cat, $lowestpoints));
                return array($cat => $lowestpoints);
            }
        }

        return false;
    }

    public function whenLastAnswer()
    {
        $query = $this->dbh->prepare("SELECT unix_timestamp(answeredts) AS answeredts FROM answers WHERE teamid<>0 ORDER BY answeredts DESC LIMIT 1");
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['answeredts'];
    }

    public function normalizer($questions)
    {
        $lead = false;
        $gameinfo = $this->getGameInfo();
        // If we don't have a lead question and if it was reset >5mins ago, then elect one
        if (empty($gameinfo['leadcat']) && empty($gameinfo['leadpoints'])) {
            if (time()-$gameinfo['lastleadreset'] > 300) { // 5 minutes
                $lead = $this->electNewLeadQuestion($questions);
            }
        } else if ($this->getNumberOfAnswers($gameinfo['leadcat'], $gameinfo['leadpoints']) > 0) {
            // If the current lead question has answers, then elect a new lead question
            $lead = $this->electNewLeadQuestion($questions);
        } else if (time()-$this->whenLastAnswer() > 1200) { // 20 minutes
            // If nobody answered in the last 20 mins, open a new (lead) question
            $this->insertAnswer(0, $gameinfo['leadcat'], $gameinfo['leadpoints']);
            $lead = $this->electNewLeadQuestion($questions);
        }
        return $lead;
    }

    public function resetLeadQuestion($teamid, $postcat, $postquestion)
    {
        $query = $this->dbh->prepare(
            "UPDATE game
               SET leadcat='', leadpoints=0, yellowshirt=?, lastleadreset=now()
             WHERE leadcat=? AND leadpoints=?"
        );
        return $query->execute(array($teamid, $postcat, $postquestion));
    }

    public function setNewLeadQuestion($teamid, $leadcat, $leadquestion)
    {
        $query = $this->dbh->prepare("UPDATE game SET leadcat=?,leadpoints=? WHERE leadcat='' AND leadpoints=0 AND yellowshirt=?");
        return $query->execute(array($leadcat, $leadquestion, $teamid));
    }

    public function startGame($conf)
    {
        $query = $this->dbh->prepare("UPDATE game SET end=date_add(now(), INTERVAL " . $conf['game']['duration'] . " SECOND)");
        return $query->execute();
    }

    public function endGame()
    {
        $query = $this->dbh->prepare("UPDATE game SET end=now()");
        return $query->execute();
    }

    public function pauseGame($min)
    {
        $query = $this->dbh->prepare("UPDATE game SET end=date_add(end, INTERVAL $min MINUTE)");
        return $query->execute();
    }

    public function listAllAnswers()
    {
        $query = $this->dbh->prepare("SELECT cat, question, points, teamid, answeredts FROM answers WHERE teamid<>0 ORDER BY answeredts");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClosedQuestions()
    {
        $query = $this->dbh->prepare(
            "SELECT count(*) as c, cat, question, teamid, answeredts FROM
            (SELECT * FROM answers ORDER BY answeredts DESC) AS j GROUP BY cat,question
            HAVING c = (SELECT count(*) FROM teams WHERE team IS NOT NULL) ORDER BY answeredts DESC"
        );
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addNewHint($string) {
        $query = $this->dbh->prepare("INSERT INTO hints (hint) VALUES (?)");
        $query->execute(array($string));
    }

    public function removeHint($id) {
        $query = $this->dbh->prepare("DELETE FROM hints WHERE id=?");
        $query->execute(array($id));
    }

}
