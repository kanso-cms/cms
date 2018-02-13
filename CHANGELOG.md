--------------------------------------------------------

### 1.2.1 <small> (14-02-2018)</small>

Update using ```git fetch```.

#### New

* Added new `CHANGELOG.md` file.
* Added new `Database::connections()` method.
* Added new `ConnectionHandler::getLog()` method.

#### Changes

* New `send_response` option in the `application` configuration:
	- Added new `send_response` option to the `application`.
	- Added third optional `boolean` parameter to `Router::__construct()` method. Defaults to `TRUE`.
	- Router now only throws `NotFoundException` if `send_response` is set to `FALSE`.
	- Application will only send response if `send_response` is set to `TRUE`.
* Added new `Environment::REQUEST_TIME` property.
* Added new `Environment::REQUEST_TIME_FLOAT` property.
* Bumped `Kanso::VERSION` number.

#### Bugfixes

* Changed internal logic of `Markdown::plainText` method.
* Added missing `DOMDocument` and `Exception` namespace importing to `ParsedownExtra` class.
* Fixed `ConnectionHandler::prepareQueryForLog` method.
* Fixed `ErrorLogger::write` method to append log to file contents.

--------------------------------------------------------