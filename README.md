# Kanso
Kanso is a lightweight CMS written in PHP with a focus on simplicity, usability and of course writing. Kanso's core premise is to bring enjoyment back to writing content online. As such, Kanso has a beautifully designed writing application as well as a fully-fledged back-end.

Kanso is primarily aimed at use for PHP-savvy front-end developers and back-end developers alike. Kanso is NOT aimed at being a client-ready CMS.

**Kanso is still under development and has not yet been through beta testing. As such, Kanso should not be used under production environments**.

## Core Features

Kanso comes packed with features, but still emphasizes simplicity. Kanso's core philosophy is to supply a solid lightweight CMS or framework with more of what you need and less of what you don't. The following is a list of some of Kanso's major features.

**Simplicity**
- Kanso's simplicity, quick installation and ease of use makes it possible for you to get online and get publishing, quickly, without even having to write a single line of code.

**Flexibility**
- Kanso is flexible and highly hackable. You can use it as a fully fledged CMS, or just a framework to build more complicated apps and websites.

**Back-end GUI**
- Kanso ships with a fully functional back-end GUI making it super easy to see all your posts, pages, comments, taxonomy and lots more.

**WordPress-like Theme System**
- Kanso uses a WordPress-like push/pull theme system to help you get started quickly on your blog or website. For developers, this means for the most part you won't even need to learn a lot of new syntax as a lot of the public methods carry over the same or similar names as WordPress. 

**Features List**
- Built on push/pull OOP PHP architecture
- Application framework
- Back-end GUI
- Strong back-end security
- Extensively commented code
- Developer ready
- Simple configuration and installation
- Extensive documentation for developers
- WordPress-link theme system
- Post importing
- Multi-privileged user accounts
- Drag'n drop image uploading
- Built in email support
- CDN Ready
- Built in threaded comment system
- Comment SPAM protection
- Blacklist/Whitelist commenters by IP address
- Powerful router
    * Standard and custom HTTP methods
    * Route parameters with wildcards and conditions
    * Route redirect, halt, and pass
- Resource Locator and DI container
- Template rendering with custom views
- Error handling and debugging
- Event and hook architecture
- HTML and HTTP response caching
- Built in session support

## Back-End GUI Features
Kanso comes with a beautifully designed, user-friendly, clutter-free back-end right out the box! The back-end has a big focus on the writing experience with a markdown-supported writer application for people that are serious about online publishing and want a clutter-free writing environment. The following are some of the major features of Kanso's back-end:


**Posts and Pages List**
- Delete, edit, publish and sort posts and pages easily.

**Taxonomy List**
- Delete, edit, clear, rename and sort tags and categories easily.

**Comments List**
- Delete, edit, clear, rename, sort and reply to comments easily.

**Account Settings**
- Update username, password or email address.
- Update authorship details - drag'n'drop profile picture, slug, name, bio and social-media links.

**Kanso Settings**
- Change Site title
- Change theme
- Update permalinks structure
- Update posts per page
- Update image upload thumbnail sizes
- Update image upload quality
- Update sitemap url
- Enable/disable category, tags and author post listings
- Enable/disable built in comments
- Enable/disable CDN support
- Enable/disable HTML caching

**User/Account Management**
- Invite new users by email address and role.
- Change existing account roles.
- Delete existing users.

**Writer**
- Markdown supported writer
- Edit or create new posts and pages
- Drag'n drop image uploading
- Title
- Hero image
- Tags
- Category
- Post/Page Type
- Excerpt
- Enable/Disable Built in comments

**Tools**
- Batch import articles via JSON file upload
- Batch upload images
- Restore installation to factory settings
    
## Requirements
- PHP 5.3.6+
- pdo_mysql
- MySQL 5.2+

To determine your PHP version, create a new file with this PHP code: 
```php
<?php 
echo PHP_VERSION;
?>
```
This will print your version number to the screen.

### Install

Note: If you intend to use Kanso as a framework ONLY, edit `Install.sample.php` set `KANSO_RUN_MODE = FRAMEWORK` and skip steps 3.

1. Ensure that you have the required components.
2. Download and unzip the Kanso package if you haven't already. Download Kanso either from here or by cloning the GitHub repository at [https://github.com/joey-j/Kanso](https://github.com/joey-j/Kanso).
3. Create a database for Kanso on your web server, as well as a MySQL user who has all privileges for accessing and modifying it.
4. Find and rename `Install.sample.php` to `Install.php`, then edit the file and add your database information.
5. Upload the Kanso files to the desired location on your web server:
 * If you want to integrate Kanso into the root of your domain (e.g. http://example.com/), move or upload via FTP/SFTP (or whatever upload method you prefer) all contents of the unzipped Kanso directory into the root directory of your web server.
 * If you want to have your Kanso installation in its own subdirectory on your website (e.g. http://example.com/blog/), create the blog directory on your server and upload the contents of the unzipped Kanso package to the directory. You'll also need to move or upload the supplied `.htaccess` file and `index.php` to your domain's root.
6. Run the Kanso installation script by accessing the URL in a web browser. This should be the URL where you uploaded the Kanso files.
 * If you installed Kanso in the root directory, you should visit: http://example.com/
 * If you installed Kanso in its own subdirectory called blog, for example, you should visit: http://example.com/blog/

That's it! Kanso should now be installed.

### Contributing

See https://github.com/joey-j/Kanso/blob/master/CONTRIBUTING.md

Full documentation to come.
