#!/usr/bin/env php
<?php

$line = readline('Password: ');
$password = trim($line, "\n");
$hash = password_hash($password, PASSWORD_DEFAULT);

switch ($format = ($argv[1] ?? 'raw')) {
case 'raw':
	die($hash ."\n");
case 'php-string':
	var_export($hash); echo "\n";
	die();
case 'php-script':
	$code = '<?php function private_function_password_hash() { return ' .var_export($hash, $return = true) .'; };' ."\n";
	$a = token_get_all($code,  TOKEN_PARSE);
	echo $code;
	die();
default:
	throw new \RuntimeException(sprintf('unspupported format "%s"', $format)); }
