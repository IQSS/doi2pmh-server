# Prerequisite

The default installation use Docker containers and has been tested with MariaDB 10.3.

## Installation of Docker on Ubuntu

* Follow https://docs.docker.com/install/linux/docker-ce/ubuntu/
* Then add yourself to the docker group with `sudo usermod -aG docker [your account]`
* Reboot your computer.

## Installation of Docker Compose

* Follow https://docs.docker.com/compose/install/ (Linux part)

## Installation of make

* On Ubuntu run this command line:

``sudo apt-get install build-essential``

## Installation of dependencies

* Go to the Makefile folder and run:

``make install``

This will build docker images if needed and install php and node dependencies.

## Start application

* Go to the Makefile folder and run ``make start`` to start containers
* You can change if you wish the administrator email in [`doi2pmh-server/symfony/src/Datafixtures/BootstrapFixtures.php`](../symfony/src/DataFixtures/BootstrapFixtures.php) with your email.
* `make install` to install the libraries
* `make symfo doctrine:migration:migrate` to create database
* `make symfo "doctrine:fixture:load --append"` to load fixtures (create the administrator user)
* `make yarn encore dev` to build js and css files
* `make symfo "lexik:jwt:generate-keypair --overwrite"` to create the JWT keypair
* Go to https://app.localtest.me and login with `admin@example.com` or `user@example.com` as email and `oaipmh` as password (assuming you didn't change the BootstrapFixtures).
You can change the administrator password in your profile.

[Back to summary](./00-summary.md)
