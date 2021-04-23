<?php

header('HTTP/1.1 500 Internal Server Error');

	# fill in with PHP passwor_hash()
$hash = '???';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

function td(...$a) { foreach ($a as $v) var_dump($v); die('td()'); }

$a = json_decode(file_get_contents('php://input'), $associative = true);
if ($a === null)
	throw new Exception('paiload decode failure');

$v = password_verify($a['meta']['auth']['password'] ?? null, $hash);
if ($v !== true)
	throw new Exception('auth failure');

foreach ($a['files'] as $pn => $encoded_body) {
		# FIXME - need security check on '/../' received from remote
	$pn = '../' .$pn;
	if (!is_dir(dirname($pn)))
		mkdir(dirname($pn), 0777, $recursive = true);
	$tmp = dirname($pn) .'/.' .basename($pn) .'.dxrecv';
	$v = file_put_contents($tmp, base64_decode($encoded_body));
	if ($v === false) {
		unlink($tmp);
		throw new Exception('could not store temporary file'); }
	$v = rename($tmp, $pn);
	if ($v === false) {
		unlink($tmp);
		throw new Exception('cound not store uploaded file'); }
}

header('HTTP/1.1 200 OK');
