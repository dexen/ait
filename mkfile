MKSHELL=rc

ait:VQ: ait.php

ait.php:VQD:
	arching -o $target.tmp --source-map $target.map src src/C -- src/ait/main.php
	arching -o $target --apply-source-map $target.map -- $target.tmp
	rm -f $target.map $target.tmp

sait:VQ: sait-AAA.php

sait-%.php:VQD: build/tmp/sait-%-inline-data.php
	arching -o $target.tmp --source-map $target.map src src/C -- src/sait/main.php < build/tmp/sait-$stem-inline-data.php
	arching -o $target --apply-source-map $target.map -- $target.tmp
	rm -f $target.map $target.tmp

build/tmp/sait-%-inline-data.php:VQD:
	php tools/preferences-for-script.php sait-$stem.php | php tools/script-preferences-script.php php-script > $target

test-build:VQ: ait sait
	mk clean

clean:VQ:
	rm -rf sait-*.php ait.php build/tmp/*.php
