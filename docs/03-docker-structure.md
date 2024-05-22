# Docker structure
This project use `docker compose` to handle the containers.
The `docker-compose.yml` file contains 5 services:
* `lb` proxy/load balancer to handle http request.
* `database` contains database
* `node` contains node modules
* `app` contains php and source code
* `mailhog` contains mailhog to test emails

## `lb`
Traefik (pronounced traffic) is a modern HTTP reverse proxy and load balancer that makes deploying microservices easy.

Repository: https://github.com/traefik/traefik

Documentation: https://doc.traefik.io/traefik/

Image: https://registry.hub.docker.com/_/traefik tag v2.3

Configuration file: [`doi2pmh-server/docker/traefik/traefik.yml`](../docker/traefik/traefik.yml)


## `database`
The database service loads the [`.env`](../.env) file data to set mysql variables.
No need to edit the `docker-compose.yml` file edit the [`.env`](../.env) file instead.

Image: https://registry.hub.docker.com/_/mariadb tagg 10

## `node`
The node service is used to install and store node dependencies.

Image: [`doi2pmh-server/docker/node/Dockerfile`](../docker/node/Dockerfile)

The folder [`doi2pmh-server/docker/node/files`](../docker/node/files) is copied into the container and contains the node install script.

## `app`
The app service container contains php installations, dependencies and source code.

Image: [`doi2pmh-server/Dockerfile`](../Dockerfile)

The following configuration is present in [`doi2pmh-server/docker/php/files`](../docker/php/files) and copied in the image:
* Composer install script
* [`php.ini`](../docker/php/files/usr/local/etc/php/conf.d/php.ini) configuration file
* Apache configuration file [`000-default.conf`](../docker/php/files/etc/apache2/sites-enabled/000-default.conf).

## `mailhog`

Mailhog is a simple SMTP server for email testing.

Image: https://registry.hub.docker.com/r/mailhog/mailhog tag latest
Repository: https://github.com/mailhog/MailHog

[Back to summary](./00-summary.md)




