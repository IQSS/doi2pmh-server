FROM php:8.1-apache
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get update && apt-get install -y \
        apt-utils \
        gnupg \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg-turbo-progs \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        msmtp \
        pngquant \
        python-is-python3 \
        rsync \
        sudo \
        unzip \
        wget \
        zlib1g-dev \
    && docker-php-ext-install \
        bcmath \
        curl \
        exif \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        soap \
        zip \
        xml

RUN a2enmod rewrite

ENV SYMFONY_ROOT="/var/www/html/"

WORKDIR $SYMFONY_ROOT

COPY symfony/ $SYMFONY_ROOT

COPY docker/php/files/ /
RUN chmod u+x /php-install.sh
