MKHELL=rc

ait:VQ:
	arching src src/C -- src/ait/main.php -o ait.php

ait-dev:VQ:
	echo '<?php ini_set("include_path", __DIR__ ."/src" .":" .ini_get("include_path")); require "src/ait/main.php";' > ait.php

sait:VQ: sait-AAA.php

sait-%.php:VQ:
	arching src src/C -- src/sait/main.php -o $target
