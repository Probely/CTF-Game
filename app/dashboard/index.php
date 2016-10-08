<?php
require '../bootstrap.php';


$db   = new DB($conf['db']);
$game = new Game($db);

$board = $game->dashboard($conf['qa']);
$secs2end = $game->endTS() - time();

// Make the leaderboard
$leaderboard = $game->dashleaderBoard();

// Board Template
require 'templates/dashboard.html';

/*
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br><br><pre><br><br><br><br><br><br><br><pre>"; print_r($board);print_r($leaderboard);
echo "<br><br><br>\n\n\n\n\n\n". $game->dashjson($board);
*/
