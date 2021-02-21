<?php

function H($str) { return htmlspecialchars($str); }
function U($str) { return rawurlencode($str); }
function td(...$a) { echo '<pre>'; foreach ($a as $v) echo H(print_r($v, true)); die('td()'); }
function tp(...$a) { echo '<pre>'; foreach ($a as $v) echo H(print_r($v, true)); echo "</pre>\ntp()\n"; return $a[0] ?? null; }


	# preserve the slashes, i.e.,
	# 'foo/bar baz.jpeg' => 'foo/bar%20baz.jpeg'
function UP(string $str) : string { return str_replace('%2F', '/', rawurlencode($str)); }
