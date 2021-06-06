<?php

header('HTTP/1.1 500 Internal Server Error');

	# fill in with PHP passwor_hash()
$hash = 'placeholder_for_password_hash_2d025152-8434-4947-9942-e3ee4643f82e';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

function td(...$a) { foreach ($a as $v) var_dump($v); die('td()'); }

$a = json_decode(file_get_contents('php://input'), $associative = true);
if ($a === null)
	throw new Exception('paiload decode failure');

$v = password_verify($a['meta']['auth']['password'] ?? null, $hash);
if ($v !== true)
	throw new Exception('auth failure');

if (!empty($a['upgrade']['sait'])) {
	$rcd = $a['upgrade']['sait'];
	$target = basename($a['upgrade']['sait'][0]);
	$tmp = 'sait-' .$target;
	$code = base64_decode($a['upgrade']['sait'][2]);
	token_get_all($code, TOKEN_PARSE);
	if (!file_put_contents($tmp, $code)) {
		unlink($tmp);
		throw new Exception('upgrade failed (1)'); }
	if (!rename($tmp, $target)) {
		unlink($target);
		unlink($tmp);
		throw new Exception('upgrade failed (2)'); }
}

foreach ($a['files'] as list($pn, $attributes, $encoded_body)) {
		# FIXME - need security check on '/../' received from remote
	if (file_exists($pn) && (filemtime($pn) == $attributes[0]) && (filesize($pn) == $attributes[1]))
		continue;
	if (!is_dir(dirname($pn)))
		mkdir(dirname($pn), 0777, $recursive = true);
	$tmp = dirname($pn) .'/.' .'sait-' .basename($pn);
	$v = file_put_contents($tmp, base64_decode($encoded_body));
	if ($v === false) {
		unlink($tmp);
		throw new Exception('could not store temporary file'); }
	touch($tmp, $attributes[0]);
	$v = rename($tmp, $pn);
	if ($v === false) {
		unlink($tmp);
		unlink($pn);
		throw new Exception('cound not store uploaded file'); }
}

header('HTTP/1.1 200 OK');
