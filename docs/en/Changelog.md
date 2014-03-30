# Changelog included since version 4.0
=======================

## Version 4.0

* Rewrote the entire codebase to be cleaner.
* Further splitted the getCMSFields into cleaner, more usable functions.
* Minor improvements on security settings.
* Minor bugfixes on set/get methods.
* Cleaned up the handle and allowed functions.
* Set permissions via permission provider.
* Removed NOW() as an SQL-statement. (appearantly, due to escaping, it doesn't work)
* Added the docs

## Version 3.3

* Remodeled the relationships.
* Lowered the amount of queries needed for each model.
* Added the AuthorHelper linking. (Author is not yet functional in the frontend)
* Switched to protected set/get methods.
* Cleaned up a lot of code for better readability.
* Moved the getCMSFields to separate classes.
* Improved rewrites/functions to better use the Framework instead of handling thing manually.
* Included naming of the URL Actions.
* Cleaned up some code.

## Version <= 3.2

* Cleaned up a lot of code.
* Added translations.
* Fixed translations.
* Added Transifex.
.
. A lot happened here, between 1.0 and 3.x
.
* Initial release.