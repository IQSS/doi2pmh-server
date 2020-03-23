#!/bin/bash

##################################################
# Quickstart: Just run this script through wget (make sure to specify the correct branch!) and bash
# (tested on a fresh install of Ubuntu 18.04 LTS)
# wget https://raw.githubusercontent.com/IQSS/doi2pmh-server/master/install.ubuntu18.sh && bash -x install.ubuntu18.sh
# -> You have to confirm with "y" on both "execute database migration"
#    and "database doi2pmh_db will be purged".
#
#    MAKE SURE NOT TO ACCIDENTALLY DO THIS IN A PRODUCTION ENVIRONMENT :)
##################################################

echo "# Install docker (and docker-compose)"
echo "## using snap (on Ubuntu)"
snap install docker

echo "# Clone the repo"
git clone https://github.com/IQSS/doi2pmh-server
cd doi2pmh-server

echo "# Start containers"
docker-compose up -d web database tools

echo "# Wait 10 seconds for the database to become available"
sleep 10

echo "# Install the app"
docker-compose run --rm tools composer install
docker-compose run --rm tools yarn
docker-compose restart tools

echo "# Set up the database"
docker-compose exec tools php bin/console doctrine:migrations:migrate
docker-compose exec tools php bin/console doctrine:fixtures:load
