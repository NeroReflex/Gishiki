#!/bin/bash
pecl config-set php_ini .travis.php.ini
pear config-set php_ini .travis.php.ini
pecl install mongodb-1.1.8
phpenv config-add .travis.php.ini