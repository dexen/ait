#!/usr/bin/env php
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$rcd = fgetcsv(STDIN, 0, "\t");
if (count($rcd) !== 4)
	throw new \RuntimeException('unsupported record format');

$site_url = $rcd[0];
$script_name = $rcd[1];
$datetime = $rcd[2];

if ($site_url[strlen($site_url)-1] !== '/')
	throw new \RuntimeException(sprintf('expected site url to end with "/", current site url: "%s"', $site_url));
$script_url = $site_url .$script_name;

$password = $rcd[3];
if (strlen($password) < 16)
	throw new \RuntimeException(sprintf('password too short: %d; expected at least %d', strlen($password), 16));
$hash = password_hash($password, PASSWORD_DEFAULT);

switch ($format = ($argv[1] ?? 'raw')) {
case 'password-hash-raw':
	die($hash ."\n");
case 'password-hash-php-string':
	var_export($hash); echo "\n";
	die();
case 'php-script':
	$code =
'<?php
function private_function_password_hash() : string { return ' .var_export($hash, $return = true) .'; };
function private_function_sait_preference_script_url() : string { return ' .var_export($script_url, $return = true) .'; };
function private_function_sait_preference_script_name() : string { return ' .var_export($script_name, $return = true) .'; };
';
	$a = token_get_all($code,  TOKEN_PARSE);
	echo $code;
	die();
default:
	throw new \RuntimeException(sprintf('unspupported format "%s"', $format)); }
