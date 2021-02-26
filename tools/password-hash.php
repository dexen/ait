#!/usr/bin/env php
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$line = readline('Password: ');
$password = trim($line, "\n");
if (strlen($password) < 16)
	throw new \RuntimeException(sprintf('password too short: %d; expected at least %d', strlen($password), 16));
$hash = password_hash($password, PASSWORD_DEFAULT);

switch ($format = ($argv[1] ?? 'raw')) {
case 'raw':
	die($hash ."\n");
case 'php-string':
	var_export($hash); echo "\n";
	die();
case 'php-script':
	$code = '<?php
function private_function_password_hash() { return ' .var_export($hash, $return = true) .'; };' ."\n";
	$a = token_get_all($code,  TOKEN_PARSE);
	echo $code;
	die();
default:
	throw new \RuntimeException(sprintf('unspupported format "%s"', $format)); }
