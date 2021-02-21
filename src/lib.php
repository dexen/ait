<?php

function H($str) { return htmlspecialchars($str); }
function td(...$a) { echo '<pre>'; foreach ($a as $v) echo H(print_r($v, true)); die('td()'); }
function tp(...$a) { echo '<pre>'; foreach ($a as $v) echo H(print_r($v, true)); echo "</pre>\ntp()\n"; return $a[0] ?? null; }
