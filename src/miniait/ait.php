#!/usr/bin/env php
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

	function files() : Generator /* of [ PATHNAME, BODY ] records */
	{
		while (!feof($this->stream)) {
			$pathname = trim(fgets($this->stream), "\n");
			if ($pathname === '')
				continue;
			yield [ $this->remotePathname($pathname), file_get_contents($pathname) ]; }
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
foreach ($Input->files() as list($pn, $body))
	$files[$pn] = base64_encode($body);

$h = curl_init($Input->url());
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
curl_setopt($h, CURLOPT_MAXREDIRS, 0);
curl_setopt($h, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
curl_setopt($h, CURLOPT_POSTFIELDS, json_encode(compact('meta', 'files'),
	JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES
		| JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ));
curl_setopt($h, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
$v = curl_exec($h);
if ($v === false) {
	++$status;
	printf("Error: cURL returned %s\n", curl_error($h)); }
$a = curl_getinfo($h);
if ($a['http_code'] !== 200) {
	++$status;
	printf("Error: HTTP status: %s\n", $a['http_code']); }

echo $v;

die($status);
