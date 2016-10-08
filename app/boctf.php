<?php
require('../vendor/autoload.php');
require 'bootstrap.php';

use WebSocket\Client;

$db   = new DB($conf['db']);
$game = new Game($db);

if (isset($_POST['start'])) {
    $db->startGame($conf);
} else if (isset($_POST['end'])) {
    $db->endGame();
} else if (isset($_POST['pause']) && isset($_POST['text'])) {
    $db->pauseGame(intval($_POST['text']));
} elseif (isset($_POST['addhint']) && isset($_POST['text'])) {
    $db->addNewHint(trim($_POST['text']));
}

// Redirect page to avoid multiple submits on refresh
if (!empty($_POST)) {
    // Force clients to reload
    $game->forceClientRefresh();

    header('Location:'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    die();
}

$board = $game->dashboard($conf['qa']);
$secs2end = $game->endTS() - time();

// Make the leaderboard
$leaderboard = $game->dashleaderBoard();

// Board Template
require 'templates/boctf.html';

/*
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br><br><pre><br><br><br><br><br><br><br><pre>"; print_r($board);print_r($leaderboard);
echo "<br><br><br>\n\n\n\n\n\n". $game->dashjson($board);
*/
