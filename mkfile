ait:VQ:
	cat src/ait/main.php > ait.php

ait-dev:VQ:
	echo '<?php ini_set("include_path", __DIR__ ."/src" .":" .ini_get("include_path")); require "src/ait/main.php";' > ait.php
