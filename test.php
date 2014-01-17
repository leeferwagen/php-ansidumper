<?php

require('lib/AnsiDumper.php');

define('PD_STREAM', fopen('/tmp/pd.log', 'a+'));

PD::val('abc');
PD::val($_SERVER);
