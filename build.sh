#!/bin/bash

here=$(dirname $(readlink -f $0))
cd $here
rm -rf build;
rsync -r "$here"/ build
cd build;

mv composer.phar composer.phar.keep

rm *.phar
rm *.phar.gz

mv composer.phar.keep composer.phar


echo -n "current space usage: ";
du . -sh
rm domains.txt
rm mail-config.php
rm -rf .idea
rm -rf .git
rm -rf test
rm -rf vendor/*/*/.git
rm -rf vendor/*/*/test
rm -rf vendor/*/*/tests
rm -rf vendor/*/*/Tests
rm -rf vendor/*/*/docs
rm -rf vendor/*/*/doc

echo -n "current space usage: ";
du . -sh

php -dphar.readonly=0 build.php

cd $here;

version=$(php version_echo.php)

cp build/$version.phar .
chmod +x $version.phar

rm -rf build
