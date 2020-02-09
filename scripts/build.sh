docker-compose run --rm tools composer install
docker-compose run --rm tools yarn
docker-compose run --rm tools yarn build
docker-compose run --rm -v $PWD/:/local tools cp -r "vendor/" /local/
