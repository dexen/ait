MKSHELL=rc

ait:VQ:
	arching src src/C -- src/ait/main.php -o ait.php

sait:VQ: sait-AAA.php

sait-%.php:VQD:
	arching src src/C -- src/sait/main.php -o $target

test-build:VQ: ait sait
