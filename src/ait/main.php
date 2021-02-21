<?php

require 'lib.php';
require 'C/AitException.php';
require 'C/ScriptTuple.php';
require 'C/Config.php';
require 'C/Connection.php';
require 'C/Uploader.php';

function showHelp()
{
	echo "ait: upload files and directories to server\n";
	echo "usage: php ait.php CONFIG_FILE SERVER_URL UPLOAD_SOURCE ...\n";
}

if (in_array($argv[1]??null, [ '-h', '--help']))
	die(showHelp());

$Config = new Config($argv[1]);
$Connection = new Connection($Config->configForServer($argv[2]));
$Uploader = new Uploader($Connection);

$fromA = array_slice($argv, 3);

function upload_dir(Uploader $Uploader, string $fromPN)
{
	tp(sprintf('should upload dir "%s"', $fromPN));
}

function tracef(string $str, ...$a)
{
	printf($str ."\n", ...$a);
}

function upload_file(Uploader $Uploader, string $fromPN)
{
	tracef('uploading file "%s"...', $fromPN);

	$Uploader->postFile($fromPN, file_get_contents($fromPN), filemtime($fromPN), fileperms($fromPN));

	tracef('done uploading file "%s".', $fromPN);
}

try {
	foreach ($fromA as $fromPN)
		if (is_file($fromPN))
			upload_file($Uploader, $fromPN);
		else if (is_dir($fromPN))
			upload_dir($Uploader, $fromPN);
		else
			throw new \RuntimeException(sprintf('neither file nor directory, cannot upload: "%s"', $fromPN)); }
catch (AitException $E) {
	echo 'Upload failed: ' .$E->getMessage() ."\n";
	die(1); }
