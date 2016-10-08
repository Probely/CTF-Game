#!/usr/bin/php -q
<?php

require '../bootstrap.php';

$db   = new DB($conf['db']);

$lead = $db->normalizer($conf['qa']);
print_r($lead);
