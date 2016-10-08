<?php

/* =========== DB INFO ============= */

$_db_host='localhost';
if (array_key_exists("DB_HOST", $_ENV)) {
    $_db_host = $_ENV['DB_HOST'];
}

$conf['db']['user'] = 'pixels';
$conf['db']['pass'] = 'xxxxxxxx';
$conf['db']['dsn'] = 'mysql:dbname=CTF;host='.$_db_host;

$conf['websocket'] = 'wss://game-ctf.rules/ws';
$conf['websocket-key'] = 'xxxxxxx';


/* =========== GAME SETTINGS ========= */

$conf['game']['duration'] = 10800; // 3h
$conf['whitelist'] = array(
    '/^88\.157\.233\.[0-9]+$/',
    '/^88\.157\.225\.[0-9]+$/',
);

/* =========== QUESTIONS ============= */

$conf['qa']['Web Hacking'][100]['q'] = '<p>Those guys at Nameless Corporation take everybody for fools, yet they believe nobody would ever target them. Nobody smart anyhow, from what I\'ve seen so far.
<br><br>
They have a microservice lying around with data from relevant employees. Maybe I can gather some information from it and do a bit of social engineering...
<br><br>
The service is listening at <code>http://w100-329074e6b126304d.ctf.rules</code> and I\'ve already mapped a few key methods. Also, I duped some helpdesk drone into resetting an account with my own password. Help me put these people to shame!
<br><br>
<code>GET /token</code> (basic auth, returns a temporary access token)<br>
<code>GET /users</code> (returns all employees in the rolodex, as JSON)<br>
<code>GET /users/1</code> (returns data for employee #1, in JSON, based on access rights)<br>
<code>PUT /users/1</code> (replaces employee #1\'s data with the JSON in the request body)
<br><br>
Requests to <code>/users</code> require a <code>X-API-Token</code> header or <code>token=</code> query string parameter.
<br><br>
The credentials for <code>/token</code> are the same ones used for the CTF with an username matching the team number (e.g. use "team1" as the username for team #1, etc.).</p>';
$conf['qa']['Web Hacking'][100]['a'] = 'xxxxxxx';
$conf['qa']['Web Hacking'][200]['q'] = ' Question 200 ';
$conf['qa']['Web Hacking'][200]['a'] = ' Answer 200';
$conf['qa']['Web Hacking'][300]['q'] = ' Question 300';
$conf['qa']['Web Hacking'][300]['a'] = ' Answer 300';
$conf['qa']['Web Hacking'][400]['q'] = ' Question 400';
$conf['qa']['Web Hacking'][400]['a'] = ' Answer 400';


$conf['qa']['Forensics'][100]['q'] = ' Question 100 ';
$conf['qa']['Forensics'][100]['a'] = ' Answer 100';
$conf['qa']['Forensics'][200]['q'] = ' Question 200 ';
$conf['qa']['Forensics'][200]['a'] = ' Answer 200';
$conf['qa']['Forensics'][300]['q'] = ' Question 300 ';
$conf['qa']['Forensics'][300]['a'] = ' Answer 300';
$conf['qa']['Forensics'][400]['q'] = ' Question 400';
$conf['qa']['Forensics'][400]['a'] = ' Answer 400';

$conf['qa']['Pwnable'][100]['q'] = ' Question 100 ';
$conf['qa']['Pwnable'][100]['a'] = ' Answer 100';
$conf['qa']['Pwnable'][200]['q'] = ' Question 200 ';
$conf['qa']['Pwnable'][200]['a'] = ' Answer 200';
$conf['qa']['Pwnable'][300]['q'] = ' Question 300 ';
$conf['qa']['Pwnable'][300]['a'] = ' Answer 300';
$conf['qa']['Pwnable'][400]['q'] = ' Question 400';
$conf['qa']['Pwnable'][400]['a'] = ' Answer 400';

$conf['qa']['Trivia'][100]['q'] = ' Question 100 ';
$conf['qa']['Trivia'][100]['a'] = ' Answer 100';
$conf['qa']['Trivia'][200]['q'] = ' Question 200 ';
$conf['qa']['Trivia'][200]['a'] = ' Answer 200';
$conf['qa']['Trivia'][300]['q'] = ' Question 300 ';
$conf['qa']['Trivia'][300]['a'] = ' Answer 300';
$conf['qa']['Trivia'][400]['q'] = ' Question 400';
$conf['qa']['Trivia'][400]['a'] = ' Answer 400';

