<?php

use AnsiDumper\FD;

require('vendor/autoload.php');

#define('FD_FILE', '/tmp/fd.dump');
$_SERVER['FD_FILE'] = 'fd.dump';

FD::time('foo');
usleep(rand(1000000, 2000000));
FD::timeEnd('foo');
