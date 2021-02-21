<?php

function showHelp()
{
	echo "ait: upload files and directories to server\n";
	echo "usage: php ait.php CONFIG_FILE 
}

if (in_array($argv[0]??null, [ '-h', '--help']))
	die(showHelp());
