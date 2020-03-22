FROM composer
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apk update && apk add yarn mysql-client