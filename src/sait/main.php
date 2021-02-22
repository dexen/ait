<?php

header('HTTP/1.1 500 Internal Server Error');

require 'lib.php';

function pathname_disallow_traverse_up(string $pn) : string
{
	$a = explode('/', $pn);
	foreach ($a as $segment)
		if ($segment === '..')
			throw new \RuntimeException('not supported: dot-dot');
	return $pn;
}

function store_from_upload()
{
	if (empty($_FILES['file']))
		throw new \RuntimeException('no file uploaded');
	if ( $_FILES['file']['error'])
		throw new \RuntimeException(sprintf('file upload error: %d', $_FILES['file']['error']));

	$in_dir = pathname_disallow_traverse_up($_POST['in_dir']??null);

		# basename() to avoid unexpected path traversal
	$destPN = PN(__DIR__, $in_dir, basename($_FILES['file']['name']));
	if (!is_dir(dirname($destPN))) {
		$v = mkdir(dirname($destPN));
		if ($v !== true)
			throw new \RuntimeException('failed to mkdir() for uploaded file'); }
	$v = move_uploaded_file($_FILES['file']['tmp_name'], $destPN);
	if ($v !== true) {
		unlink($destPN);	# in case of partial move
		throw new \RuntimeException('failed to move uploaded file'); }

	$v = chmod($destPN, $_POST['perms']);
	if ($v !== true) {
		unlink($destPN);
		throw new \RuntimeException('failed to chmod() uploaded file'); }

	$v = touch($destPN, $_POST['mtime']);
	if ($v !== true) {
		unlink($destPN);
		throw new \RuntimeException('failed to touch() uploaded file'); }
}

if ($_POST)
	store_from_upload();

header('HTTP/1.1 200 OK');

echo '<!DOCTYPE html>';
echo '<html>';
echo '<body>';
echo '<h1>Welcome to sait</h1>';

echo '<p><em>Local files:</em></p>';

echo '<table>';

foreach (glob('*', GLOB_ERR) as $pn)
	echo '<tr><td><a href="' .H(UP($pn)) .'">' .H($pn) .'</a></td><td>' .H(date('Y-m-d H:i:s', filemtime($pn))) .'</td></tr>';
