<?php

require 'lib.php';
require 'C/ScriptTuple.php';
require 'C/Config.php';
require 'C/Connection.php';

function showHelp()
{
	echo "ait: upload files and directories to server\n";
	echo "usage: php ait.php CONFIG_FILE SERVER_URL UPLOAD_SOURCE ...\n";
}

if (in_array($argv[1]??null, [ '-h', '--help']))
	die(showHelp());

$Config = new Config($argv[1]);
$Connection = new Connection($Config->configForServer($argv[2]));
