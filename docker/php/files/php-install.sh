#!/bin/sh

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# SYMFONY_ROOT variable must come from Dockerfile
cd $SYMFONY_ROOT && composer install

php bin/console assets:install