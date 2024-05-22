# Configuration

## Authentification
The users are identified by their email.

By default, the users and their passwords are stored in database. If CAS authentification is enabled, DOI2PMH relies on the external CAS to authentificate the users. In this case, no password is stored by DOI2PMH.

To enable CAS authentification, the environment variable ENABLE_CAS must be set to 'true' and the following variables must be defined:
* CAS_HOST
* CAS_VERSION
* CAS_URI
* CAS_PORT

## Configuration menu

The configuration menu provides the following parameters:

* **Repository name**: name of the exposed OAI-PMH repository.
* **Administrator email**: the email exposed as contact link, including in the OAI Identify response.
* **Earliest Datestamp**: `earliestDatestamp` value in the OAI Identify response.
* **Excluded types**: allow filtering based on `dc:type` values. The matching DOI can still be added to DOI2PMH, but will not be exposed by the repository.

The **Update DOIs** button refresh the metadata of all the DOI. This is equivalent to the `doi:refresh` command.

[Back to summary](./00-summary.md)