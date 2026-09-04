FROM php:8.3-apache
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get -y update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl
COPY 000-default.conf /etc/apache2/sites-enabled
RUN mkdir -p /var/www/var/log && mkdir -p /var/www/var/cache/dev && mkdir -p /var/www/var/cache/prod && chmod -R 777 /var/www/var