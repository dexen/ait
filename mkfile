MKSHELL=rc

ait:VQ:
	arching src src/C -- src/ait/main.php -o ait.php

sait:VQ: sait-AAA.php

sait-%.php:VQD: build/tmp/sait-%-inline-data.php
	arching src src/C -- src/sait/main.php -o $target < build/tmp/sait-$stem-inline-data.php

build/tmp/sait-%-inline-data.php:VQD:
	php tools/preferences-for-script.php sait-$stem.php | php tools/script-preferences-script.php php-script > $target

test-build:VQ: ait sait
	mk clean

clean:VQ:
	rm -rf sait-*.php ait.php build/tmp/*.php
