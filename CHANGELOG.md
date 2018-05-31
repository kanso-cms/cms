--------------------------------------------------------

### 1.4.1 <small> (22-03-2018)</small>

Update using ```git fetch```.

#### New
* Media library in the admin panel uses CMS thumbnail_sizes configuration to build dropdown menu for attachments.

#### Changes
* Bumped `Kanso::VERSION` number.

--------------------------------------------------------

### 1.4.0 <small> (22-03-2018)</small>

Update using ```git fetch```.

#### New
* Added new `Str::queryFilterUri` function for sanitizing URLS.
* CMS uses `Str::queryFilterUri` throughout the application internals.
* Other minor bug fixes

#### Changes
* Bumped `Kanso::VERSION` number.

--------------------------------------------------------

### 1.3.0 <small> (26-02-2018)</small>

Update using ```git fetch```.

#### New
* Added new SMTP email support:
	- Added new SMTP library to CMS email under `kanso\cms\emai\phpmailer` namespace.
	- Added new `use_smtp` key under the `email.php` configuration file.
	- Added new `smtp_settings` with sub settings under the `email.php` configuration file.
* Added unit testing for SMTP email.

#### Changes
* Updated CMS to support new SMTP library:
	- Updated CMS `kanso\cms\application\services\EmailService` to support new SMTP configuration options and library.
	- Updated CMS `kanso\cms\email` to support new SMTP configuration options and library.
* Bumped `Kanso::VERSION` number.

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
