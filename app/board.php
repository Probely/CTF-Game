<?php
require 'bootstrap.php';

if (!Auth::check() || !Auth::ip_check()) {
    // If it has not a valid session then redirect to the login page
    header("Location: index.php");
    exit(0);
}

$db   = new DB($conf['db']);
$game = new Game($db);
$team = new Team($game);

$allanswered = false;
$notification = false;
$notificationmsg = '';
$error = false;
$errormsg = '';
$msg = 'You should select an open question from the board on the left!';

$board = $team->board($conf['qa']);
$hints = $game->getHints();
$secs2end = $game->endTS() - time();
//$time2end = gmdate("H:i:s", $secs2end);

$atleastoneopen = false;
foreach ($board as $cat => $que) {
    if (in_array('', $que)) {
        $atleastoneopen = true;
    }
    if (array_search('open', $que)) {
        $opencat = $cat;
        $openq = array_search('open', $que);
    }
}

//echo "<br>";var_dump($atleastoneopen);var_dump($board);

// Submitting an answer
if (isset($_POST['answer']) && $secs2end>0) {
    $category = $_POST['category'];
    $question = $_POST['question'];
    // Checking if the question is open
    if ($board[$category][$question] == 'open' ||
        $board[$category][$question] == 'lead'
    ) {

        $answer = strtolower(trim($_POST['answer']));
        $answer = preg_replace("/\r|\n/", "", $answer);

        $isCorrectAnswer = $answer === strtolower($conf['qa'][$category][$question]['a']);

        openlog('CTF', LOG_ODELAY, LOG_DAEMON);
        syslog(LOG_CRIT, "Dashboard [Team: ".$team->id()."|".$team->ip()."|".$category."|".$question."|Correct: ".($isCorrectAnswer?'true':'false')."] - ".$answer);

        // Validating the answer
        if ($isCorrectAnswer) {
            $team->answer($category, $question);
            $notification = true;
            $notificationmsg = 'Hurray! That is the <span>correct answer</span>!';

            if ($atleastoneopen) {
                $notificationmsg = $notificationmsg . ' Pick another question from the list to carry on.';
                if ($game->resetLeadQuestion($category, $question)) {
                    // non error
                }
            }
            $game->reloadGameInfo();
        } else {
            $error = true;
            $errormsg = 'Wrong answer. Try again :(';
        }
    }
} else if (isset($_GET['newleadcat']) && isset($_GET['newleadq'])
    && $game->isSelectLeadMode() && $secs2end>0
) { // Selected a new lead question
        // Checking if the question is not opened
    if (empty($board[$_GET['newleadcat']][$_GET['newleadq']])) {
        if ($game->setNewLeadQuestion($_GET['newleadcat'], $_GET['newleadq'])) {
            // non error
            $category = $_GET['newleadcat'];
            $question = $_GET['newleadq'];
        }
         $game->reloadGameInfo();
    }
} else { // Loading a question
    // Check if the question is open. If not, return lead question
    if (isset($_GET['cat']) && isset($_GET['q']) && $board[$_GET['cat']][$_GET['q']] == 'open') {
        $category = $_GET['cat'];
        $question = $_GET['q'];
    } else {
        $category = $game->leadCat();
        $question = $game->leadPoints();
        //echo "<pre><br>"; var_dump($category,$question, $opencat, $openq);
        if ((isset($board[$category][$question]) && $board[$category][$question] == 'answered')) {
            if (!isset($opencat) && !isset($openq)) {
                 $allanswered = true;
            } else {
                $category = $opencat;
                $question = $openq;
            }
        }
    }

}

// We need to retrieve the info again because of answered questions or new lead questions
$board = $team->board($conf['qa']);

// Select current question
if (!empty($category) && !empty($question)
    && ($board[$category][$question] == 'open' || $board[$category][$question] == 'lead')
) {
        $board[$category][$question] = ($board[$category][$question] == 'lead') ? 'lead current' : 'current';
}

// Make the leaderboard
$leaderboard = $game->leaderBoard();

// Selecting a new lead question?
if ($game->isSelectLeadMode()) {
    $board = $team->boardSelectLead($board);
    $notification = true;
    $notificationmsg = 'Open a new question below!';
    $msg = 'You should open a new question from the board on the left!';
}

// Board Template
require 'templates/board.html';

/*
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br><br><pre><br><br><br><br><br><br><br><pre>"; print_r($board);
echo "<br><br><br>\n\n\n\n\n\n". $game->json($board);
echo "<br><br><br><br>"; print_r($game->db->normalizer($conf['qa']));
*/
