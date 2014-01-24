<?php

require('lib/FD.php');
#define('FD_FILE', '/tmp/fd.dump');
$_SERVER['FD_FILE'] = '/tmp/fd.dump';

FD::clearScreen()->val($_SERVER)->val(microtime(true));
