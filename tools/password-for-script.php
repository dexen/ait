#!/usr/bin/env php
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$config_file = 'config.txt';
$script_name = $argv[1];

$found = [];

$h = fopen('config.txt', 'r');
while (($rcd = fgetcsv($h, 0, "\t")) !== false) {
	# comment lines start with "#"
	# record format: URL	SAIT-SCRIPT-NAME	DATE	PASSWORD

	if ($rcd[0][0] === '#')
		continue;
	if ($rcd[1] === $script_name)
		$found[] = $rcd; }

switch (count($found)) {
case 1:
	echo $found[0][3] ."\n";
	die();
case 0:
	throw new \RuntimeException(sprintf('no password found in config "%s" for script name "%s"',
		$config_file, $script_name ));
default:
	throw new \RuntimeException(sprintf('multiple conflicting matches found in config "%s" for script name "%s"',
		$config_file, $script_name )); }
