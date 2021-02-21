<?php

function H($str) { return htmlspecialchars($str); }
function U($str) { return rawurlencode($str); }

echo '<!DOCTYPE html>';
echo '<html>';
echo '<body>';
echo '<h1>Welcome to sait</h1>';

echo '<p><em>Local files:</em></p><li>';

foreach (glob('*') as $pn)
	echo '<ul><a href="' .H(U($pn)) .'">' .H($pn) .'</a></ul>';
