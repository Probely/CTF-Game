<?php
require 'bootstrap.php';

header("Content-type: Application/json");

$db   = new DB($conf['db']);
$game = new Game($db);

$board = $game->dashboard($conf['qa']);
$secs2end = $game->endTS() - time();

echo $game->dashjson($board);
