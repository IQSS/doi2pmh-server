#!/bin/sh

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# SYMFONY_ROOT variable must come from Dockerfile
cd $SYMFONY_ROOT && composer install

php bin/console assets:install

mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var