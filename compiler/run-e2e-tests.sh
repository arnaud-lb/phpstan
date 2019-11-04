#!/usr/bin/env bash
set -ev

# compile phar
php bin/compile

# setup
cd e2e
rm -rf vendor
rm -f composer.lock
composer install --no-interaction
cp -f ../../tmp/phpstan.phar vendor/phpstan/phpstan/phpstan.phar
cp -f ../../tmp/phpstan.phar vendor/phpstan/phpstan/phpstan

# test that the phar autoloader works
php testPharAutoloader.php

# test levels
vendor/bin/phpunit PharTest.php