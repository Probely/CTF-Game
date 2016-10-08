<?php
require 'bootstrap.php';

if (!Auth::ip_check()) {
    header('HTTP/1.0 403 Forbidden');
    die("Nop!");
}

// If we are trying to logout, clear the session
if (isset($_GET['reason']) && $_GET['reason'] == 'logout') {
    $_SESSION = array();
} else if (Auth::check()) {
    // If it has a valid session then redirect to the board page
    header("Location: board.php");
    exit(0);
}

$failedlogin = false;
// Trying to login
if (isset($_POST['login'])) {
    // Initialize DB and Auth class, to check authentication
    $db = new DB($conf['db']);
    $auth = new Auth($db);
    $loggedin = $auth->login($_POST['login']);
    if ($loggedin) { // auth successful
        session_regenerate_id(); // prevent session fixation
        header("Location: board.php");
        exit(0);
    } else {
        $failedlogin = true;
    }

}

// Login Template
require 'templates/index.html';
