--------------------------------------------------------

### 2.1.0 <small> (10-11-2018)</small>

Update using ```git fetch```.

#### New
* Added new `Pixl` service as default framework service `app/configurations/application.php`.
* Added new `PixlService` to framework application services.
* Added new `Pixl` configuration file in `app/configurations/pixl.php`.
* Added new `Plugin` abstract class for CMS.
* Added new `Image::loadImage` method.
* New `parent` method added to `Comment`.
* `CategoryManager::create` returns `FALSE` if category already exists.
* `TagManager::create` returns `FALSE` if tag already exists.
* `UserManager::createAdmin` returns `User` if successful.
* `UserManager::createUser` parameters changed. 
* `CategoryProvider::byKey` returns `FALSE` if category does not exist. 
* `CommentProvider::byKey` returns `FALSE` if comment does not exist. 
* `MediaProvider::byKey` returns `FALSE` if media does not exist. 
* `PostProvider::byKey` returns `FALSE` if post does not exist. 

#### Changes
* Updated to single root level `.gitignore`.
* `kanso\framework\pixl\Image` constructor file path is now optional.
* `kanso\framework\pixl\processor\GD` constructor now has optional second argument for default image quality.
* `kanso\framework\utility\Gump` replaced with `kanso\framework\validator\Validator`. See documentation for details. 

* Bumped `Kanso::VERSION` number.

#### Bugfixes

--------------------------------------------------------

### 2.0.0 <small> (01-10-2018)</small>

Update using ```git fetch```.

#### New
* Added Social Media Tag Configs.
* New Git webhooks deployment integration.
* Added Email Logs To Admin Panel.
* Added Email Logs To Admin Panel.
* Added User Agent Bot Detection To Framework.
* Added Basic CRM Component to CMS.
* New leads page in Admin Panel.

#### Changes
* Added `PHP CS Fixer` And Bin Directory.
* Ran All Code Through `PHP CS Fixer`.
* Updated Unit Tests.
* PHP 7.2 Support.
* Bumped `Kanso::VERSION` number.

#### Bugfixes
* Fix Default OpenSSL Cyphers on PHP 7.2.
* Native Session Storage `session_gc` PHP < 7 || > 7.1.0 Fixup
* Other minor bug fixes throughout the application.

--------------------------------------------------------

### 1.6.2 <small> (25-09-2018)</small>

Update using ```git fetch```.

#### Changes
* `Response` object automatically sends `Cache-Control` headers to disable browser caching of PHP generated content.
* `Response` object only sends body if request is not 'HEAD'.
* Added request method `Response` object constructor.
* Error log records `HTTP REFERRER`.
* Bumped `Kanso::VERSION` number.

#### Bugfixes
* Removed on `Wrapper` abstract redeclaring protected `data` property from `MagicArrayAccessTrait`

--------------------------------------------------------

### 1.6.1 <small> (13-09-2018)</small>

Update using ```git fetch```.

#### Changes
* Bumped `Kanso::VERSION` number.

#### Bugfixes
* Fixed `Query` parser when getting posts by `tag_id` or `category_id` 

--------------------------------------------------------

### 1.6.0 <small> (27-08-2018)</small>

Update using ```git fetch```.

#### Changes
* Bumped `Kanso::VERSION` number.
* Consolidated Admin Panel Sidebar

#### Bugfixes
* Fixed up error on empty search results

--------------------------------------------------------

### 1.5.2 <small> (25-07-2018)</small>

Update using ```git fetch```.

#### Changes
* Bumped `Kanso::VERSION` number.
* User/Author descriptions now use 'TEXT' in the database.
* ```the_author_bio``` converts new lines to ```<br>```.
* ```UserManager::create```, ```UserManager::createUser``` and ```UserProvider::create``` all return the new user instance when a new user is successfully created.

#### Bugfixes
* Fixup on ```Request::fetch``` query strings.

--------------------------------------------------------

### 1.5.1 <small> (21-06-2018)</small>

Update using ```git fetch```.

#### Changes
* Bumped `Kanso::VERSION` number.

#### Bugfixes
* Fixed category/tag filtering for incoming requests to paginated taxonomies.
* Removed and replaced ```admin_kanso_config``` function in admin panel.

--------------------------------------------------------

### 1.5.0 <small> (13-06-2018)</small>

Update using ```git fetch```.

#### New
* Added a new ```strip_tags``` function to ```Str``` class.
* ```Environment``` now has the ```REFERER``` key.

#### Changes
* Admin panel uses minified scripts and stylesheets.
* Updated logic when adding a page to the admin panel.
* The ```Email``` service is no longer a singleton and returns a new instance whenever called.
* ```NotFoundException``` defaults to a generic message for error logging if not provided.
* Bumped `Kanso::VERSION` number.

#### Bugfixes
* Fixed up category input field on admin panel for existing posts so that child categories are first in ascending order to parents.
* Added a fallback ```Gatekeeper``` for when a user cannot be found from the database.
* SMTP plain text emails keep line breaks.
* ```Attachment``` returns default url for all sizes if it is not an image.

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
