# doi2pmh-server
An OAI-PMH server that provides harvesting sets based on a list of DOIs.

**Dataverse community driven project**

## Concept
![Concept Diagram](https://user-images.githubusercontent.com/21006/72841442-5e04f200-3c64-11ea-8f9c-a494bc318fab.png)

## Background
Based on Feature Requests from the Dataverse Community:
- [When I create a dataset, I want use an existing DOI](https://github.com/IQSS/dataverse/issues/6425)
- [Harvesting DOI metadata from non-OAI-PMH sources](https://github.com/IQSS/dataverse/issues/5402)

```
# Start container
docker-compose up -d web
# Fisrt setup: download dependencies
docker-compose run tools composer install 
docker-compose run tools yarn
docker-compose restart tools
```

## Update Language files

```
php bin/console translation:update en --dump-messages --output-format yaml --force
php bin/console translation:update fr --dump-messages --output-format yaml --force
```