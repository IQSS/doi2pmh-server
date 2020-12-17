# doi2pmh-server
An OAI-PMH server that provides harvesting sets based on a list of DOIs.

**Dataverse community driven project**

## Concept
![Concept Diagram](https://user-images.githubusercontent.com/21006/72841442-5e04f200-3c64-11ea-8f9c-a494bc318fab.png)

## Background
Based on Feature Requests from the Dataverse Community:
- [When I create a dataset, I want use an existing DOI](https://github.com/IQSS/dataverse/issues/6425)
- [Harvesting DOI metadata from non-OAI-PMH sources](https://github.com/IQSS/dataverse/issues/5402)


## Development

![](https://travis-ci.org/IQSS/doi2pmh-server.svg?branch=master)

Demo deployment: [http://doi2pmh.alwaysdata.net](http://doi2pmh.alwaysdata.net)


### Quick start

You need docker and docker-compose.

```
# Start container
docker-compose up -d
# First setup: download dependencies
docker-compose run --rm tools composer install 
docker-compose run --rm tools yarn
docker-compose restart tools
# Create 2 users
docker-compose exec tools php bin/console doctrine:migrations:migrate
docker-compose exec tools php bin/console doctrine:fixtures:load
```

### How to?

#### Open shell in tools container ?

```
docker-compose exec tools bash
```


#### Update Language files

In a shell in tools container:

```
php bin/console translation:update en --dump-messages --output-format yaml --force
php bin/console translation:update fr --dump-messages --output-format yaml --force
```

#### Create entity

In a shell in tools container:

```
php bin/console make:entity
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

#### Access database

In a shell in tools container: 

```
mysql -h database -u $MYSQL_USER  -p$MYSQL_PASSWORD $MYSQL_DATABASE
```

