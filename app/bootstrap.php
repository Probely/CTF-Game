<?php

/* ===== ERROR REPORTING ======= */
error_reporting(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 0);
date_default_timezone_set("Europe/Lisbon");

/* ===== REQUIRES ======= */
require 'conf/conf.php';
require 'lib/DB.php';
require 'lib/Auth.php';
require 'lib/Game.php';
require 'lib/Team.php';

/* ===== SESSION ======= */

$currentCookieParams = session_get_cookie_params();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    false, // secure
    true   // httponly
);
session_name('ctf');
session_start();
