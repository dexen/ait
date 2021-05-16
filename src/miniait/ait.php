#!/usr/bin/env -S php -d 'memory_limit=512M'
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

function td(...$a) { foreach ($a as $v) var_dump($v); echo "--\ntd()\n"; die(1); }

$status = 0;

class StdinInput
{
		# not very secure, yuck!
	private $password;
	protected $stream;
	protected $argv;
	protected $url;
	protected $strip_segments;

	function __construct($stream, $argv)
	{
		$this->argv = $argv;
		[ $JUNK, $this->strip_segments, $this->url ] = $argv;
		$this->stream = $stream;
		$this->password = trim(fgets($this->stream), "\n");
	}

	function url() : string { return $this->url; }

	protected
	function remotePathname(string $pn) : string
	{
		$a = explode('/', pathinfo($pn)['dirname']);
		if (count($a) < $this->strip_segments)
			throw new Exception('cannot strip enough segments');

		$a = array_slice($a, $this->strip_segments);
		array_push($a, pathinfo($pn)['basename']);
		return implode('/', $a);
	}

	function files() : Generator /* of [ SOURCE_PATHNAME, DESTINATION_PATHNAME, BODY ] records */
	{
		while (!feof($this->stream)) {
			$pathname = trim(fgets($this->stream), "\n");
			if ($pathname === '')
				continue;
			yield [ $pathname, $this->remotePathname($pathname), file_get_contents($pathname) ]; }
	}

	function password() : string
	{
		return $this->password;
	}
}

$Input = new StdinInput(STDIN, $argv);

$meta = [
	'auth' => [
		'password' => $Input->password(), ] ];
$files = [];

$h = curl_init($Input->url());
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
curl_setopt($h, CURLOPT_MAXREDIRS, 0);

$next_files = [];
$next_size = 0;

$size_limit = 32*1024*1024;

while (true) {
$files = $next_files; $next_files = null;
$size = $next_size; $next_size = null;
foreach ($Input->files() as list($origPN, $pn, $body)) {
	$newsize = filesize($origPN);
	if ((count($files) >= 100) || (($size+$newsize)>=$size_limit)) {
		$next_files = [ $pn => base64_encode($body), ];
		$next_size = $newsize;
		break; }
	else {
		$files[$pn] = base64_encode($body);
		$size += $newsize; } }

if (empty($files))
	break;

$payload = json_encode(compact('meta', 'files'),
	JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES
		| JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
printf("Sending %d (%d) {%d}\n", count($files), strlen($payload)/1024, $size/1024);

curl_setopt($h, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
curl_setopt($h, CURLOPT_POSTFIELDS, $payload);
curl_setopt($h, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
$v = curl_exec($h);
if ($v === false) {
	++$status;
	printf("Error: cURL returned %s\n", curl_error($h)); }
$a = curl_getinfo($h);
if ($a['http_code'] !== 200) {
	++$status;
	printf("Error: HTTP status: %s\n", $a['http_code']);
	die($status); } }

die($status);
