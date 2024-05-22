# Development with Docker

This project use `docker compose` to handle the containers. You can use directly this command or the available Makefile for an easier usage.

Useful Docker commands:
* `docker compose ps`
  * List all running containers for this project
* `docker network ls | grep "doi2pmh"`
  * List all docker networks related to doi2pmh projects

# `make` commands

The makefile contains a list of shortcuts for more complex `docker compose` usage.
Juste run `make` in the same folder as the Makefile to have the list of available commands.

## Basic

Here are the more used ones:
* `make install`
  * Install the application dependencies (Composer and Node)
* `make start`
  * Start all containers, build them if needed, mount volumes
* `make stop`
  * Stop all containers
* `make symfo "doctrine:schema:update --force"`
  * Updates DB schema according to Symfony entity classes
* `make symfo "doctrine:fixtures:load"`
  * Run fixtures for creating some dummy data
* `make mysql`
  * Open a MySQL command line on the dedicated container
* `make shell-[service]`
  * Open an SSH prompt on container matching the service:
    * app
    * database
    * node
    * lb
    * mailhog
* `make yarn "encore dev --watch"`
  * Compile CSS/JS/Images
  * Watch files to compile on change
* `make symfo [command]`
  * Run the given command on `bin/console` symfony script in PHP container
  * If you need more arguments for this command (e.g. `--force`) you must add quotes `"` around the whole command
    * e.g.: `make symfo "doctrine:schema:update --force"`
    * 
### Update Language files

In a shell in tools container:

```
php bin/console translation:update en --dump-messages --output-format yaml --force
php bin/console translation:update fr --dump-messages --output-format yaml --force
```


## Advanced

Some commands are more advanced and used for debugging:
* `make docker-pull`
  * Force pull of remote images
* `make docker-rebuild [SERVICE]`
  * Rebuild images and don't run them
  * If SERVICE is not specified, the command will run for all of them
  * Useful when you modify Dockerfiles or `docker-compose.yml`
  * Does not imply recompiling if Docker does not consider it mandatory
* `make docker-debug [SERVICE]`
  * Same as `make docker-rebuild` but this time it also run containers in attached mode (i.e. without `-d` argument) the containers.
  * Usefull for debugging and seeing error at containers startup or execution
* `make docker-prune`
  * Removes all containers, networks and volumes of the project
* `make docker-remove`
  * Removes all containers of the project

[Back to summary](./00-summary.md)
