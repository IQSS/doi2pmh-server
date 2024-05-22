# Usage

## Folder
A folder corresponding to the `set` parameter in OAI-PMH protocol (see [OAI-PMH documentation](https://www.openarchives.org/OAI/openarchivesprotocol.html)).

A folder can contain other folders or Doi (see Doi part below).
The Doi(s) contained in the folder (and in the sunfolders) corresponding to the set filter, will be returned during harvesting.

All users with access right on a folder can add a subfolder.

## DOI
A DOI is a unique identifier representing a scientific publication. 
In DOI2PMH a Doi it's composed of a name, an uri (https://doi.org/10...) and a citation which is automatically fetched when adding a doi to a folder. 

All users with access right on a folder can add a DOI in it.

## Roles
### Administrator
An administrator can:
* View, create, edit, remove any user.
* Add, move and remove the right in any folder for any user
* View, create, edit, remove any folder
* View, create, edit, remove any Doi
* Configure, synchronise Doi's data with Datacite (Configuration page)

### User
A user can:
* View any folder and Doi in the plateforme
* Edit, remove any child folder if the user has the right in a parent folder
* Add user right to any child folder if the user has the right in a parent folder
* Add a Doi to any child folder if the user has the right in a parent folder.

## Commands

`php bin/console doi:refresh`: refresh the metadata for all the DOI. This is useful if the metadata has been updated upstream.

[Back to summary](./00-summary.md)