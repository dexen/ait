<?php

require 'src/ait/C/ScriptTuple.php';
require 'src/ait/C/Config.php';
require 'src/ait/C/Connection.php';

function showHelp()
{
	echo "ait: upload files and directories to server\n";
	echo "usage: php ait.php CONFIG_FILE 
}

if (in_array($argv[0]??null, [ '-h', '--help']))
	die(showHelp());

$Config = new Config($argv[1]);
$Connection = new Connection($Config->configForServer($argv[2]));
