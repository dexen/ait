#!/usr/bin/env -S php -d 'memory_limit=512M'
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

function td(...$a) { foreach ($a as $v) var_dump($v); echo "--\ntd()\n"; die(1); }

$status = 0;

function render_usage()
{
	echo 'Usage: INPUT_RECORDS | ait STRIP_PATHNAME_SEGMENTS TARGET_SAIT_URL
Example:
# { echo $PASSWORD; echo $SAIT_SCRIPT_PATHNAME; find s/ -type f; } | ./ait 1 https://example.com/sait-foobarbaz.php
';
}

class StdinInput
{
		# not very secure, yuck!
	private $password;
	private $sait_pn;
	protected $stream;
	protected $argv;
	protected $url;
	protected $strip_segments;
	protected $http_username;
	protected $http_password;

	function __construct($stream, $argv)
	{
		$this->argv = $argv;
		if (($argv[1]??null) === '--http-auth') {
			$this->http_username = $argv[2];
			$this->http_password = $argv[3];
			$argv = array_merge([ $argv[0] ], array_slice($argv, 4)); }
		if (array_key_exists(1, $argv)) {
			[ $JUNK, $this->strip_segments, $this->url ] = $argv;
			$this->stream = $stream;
			$this->password = trim(fgets($this->stream), "\n");
			$this->sait_pn = trim(fgets($this->stream), "\n"); }
		if (empty($this->password))
			throw new \Exception('no password provided (first line)');
		if (!is_file($this->sait_pn))
			throw new \Exception(sprintf('sait script not found, tried "%s" (second line)', $this->sait_pn));
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

	function files() : Generator /* of [ DESTINATION_PATHNAME, [ ATTRIBUTES], BODY ] records */
	{
		while (!feof($this->stream)) {
			$pathname = trim(fgets($this->stream), "\n");
			if ($pathname === '')
				continue;
			yield [ $this->remotePathname($pathname), [ filemtime($pathname), filesize($pathname) ], base64_encode(file_get_contents($pathname)) ]; }
	}

	function encoded_files() : Generator /* of [ DESTINATION_PATHNAME, [ ATTRIBUTES], BODY ] records */
	{
		foreach ($this->files() as $rcd) {
			[$rcd[0], $rcd[2]] = [base64_encode($rcd[0]), base64_encode($rcd[2])];
			yield $rcd; }
	}

	function saitRcd() : array
	{
		return [ basename($this->sait_pn), [ filemtime($this->sait_pn), filesize($this->sait_pn) ],
			base64_encode(file_get_contents($this->sait_pn)) ];
	}

	function password() : string
	{
		return $this->password;
	}

	function httpUsername() { return $this->http_username; }
	function httpPassword() { return $this->http_password; }

	function noInput() { return count($this->argv) <= 1; }
}

$Input = new StdinInput(STDIN, $argv);
if ($Input->noInput())
	render_usage() or die(1);

$meta = [
	'auth' => [
		'password' => $Input->password(), ] ];
$upgrade = [
	'sait' => $Input->saitRcd(),
];

$encoded_files = $files = [];

$h = curl_init($Input->url());
if ($Input->httpUsername() !== null)
	curl_setopt($h, CURLOPT_USERNAME, $Input->httpUsername());
if ($Input->httpPassword() !== null)
	curl_setopt($h, CURLOPT_PASSWORD, $Input->httpPassword());
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
curl_setopt($h, CURLOPT_MAXREDIRS, 0);

$next_files = [];
$next_size = 0;

$size_limit = 32*1024*1024;

while (true) {
$encoded_files = $next_files; $next_files = null;
$size = $next_size; $next_size = null;
foreach ($Input->encoded_files() as list($pn, $attributes, $body)) {
	$newsize = $attributes[1];
	if ((count($encoded_files) >= 1000) || (($size+$newsize)>=$size_limit)) {
		$next_files= [ [ $pn, $attributes, $body ] ];
		$next_size = $newsize;
		break; }
	else {
		$encoded_files[] = [ $pn, $attributes, $body ];
		$size += $newsize; } }

if (empty($encoded_files))
	break;

$payload = json_encode(compact('meta', 'upgrade', 'files', 'encoded_files'),
	JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES
		| JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
printf("Sending %d (%d) {%d}\n", count($encoded_files), strlen($payload)/1024, $size/1024);

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
	printf("Output: \"%s\"\n", $v);
	die($status); } }

die($status);
