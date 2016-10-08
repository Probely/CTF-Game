<?php
require 'bootstrap.php';

header("Content-type: Application/json");

if (!Auth::check()) {
    echo json_encode(array('error'=>true));
    exit(0);
}

$db   = new DB($conf['db']);
$game = new Game($db);
$team = new Team($game);
$board = $team->board($conf['qa']);

$allanswered = false;
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

if (!empty($category) && !empty($question)
    && ($board[$category][$question] == 'open' || $board[$category][$question] == 'lead')
) {
        $board[$category][$question] = ($board[$category][$question] == 'lead') ? 'lead current' : 'current';
}

if ($game->isSelectLeadMode()) {
    $board = $team->boardSelectLead($board);
}

$board['selectmode'] = $game->isSelectLeadMode();
$board['allanswered'] = $allanswered;

$secs2end = $game->endTS() - time();
if ($secs2end<=0) {
    $board['allanswered'] = true;
}

echo $game->json($board);
