#!/bin/bash -x

sh -c 'if [ "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ]; then echo "" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini; fi;'
sh -c 'if [ "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ] && [ $(php -r "echo PHP_MINOR_VERSION;") -le 4 ]; then echo "extension = apc.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini; fi;'

sudo locale-gen nl_BE.UTF-8 && sudo update-locale

sudo mkdir -p /home/projects
echo "USE mysql;\nUPDATE user SET password=PASSWORD('password') WHERE user='root';\nFLUSH PRIVILEGES;\n" | mysql -u root

sudo apt-get install nginx

composer install --prefer-source