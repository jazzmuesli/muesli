#!/bin/sh
echo "Downloading PHPUnit"
wget -c http://pear.phpunit.de/get/phpunit.phar || exit 1
echo "Setting permissions"
chmod +x phpunit.phar || exit 2
echo "Running PHPUnits"
./phpunit.phar UserManager.test.php && exit $?
