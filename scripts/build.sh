docker-compose run -e APP_ENV=prod --rm tools composer install
docker-compose run -e APP_ENV=prod --rm tools yarn
docker-compose run -e APP_ENV=prod --rm tools yarn build
docker-compose run -e APP_ENV=prod --rm -v $PWD/:/local tools cp -r "vendor/" /local/
