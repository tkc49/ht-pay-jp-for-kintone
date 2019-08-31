#!/bin/sh
zip -r ../payjp-for-kintone.zip ./ -x ./bin\* -x ./tests\* -x *.git* -x '.distignore' -x '.editorconfig' -x '.gitignore' -x '.phpcs.xml.dist' -x '.travis.yml' -x 'composer.json' -x 'composer.lock' -x 'Gruntfile.js' -x 'phpunit.xml.dist' -x 'zip.sh'
