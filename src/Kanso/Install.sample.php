<?php

/********************************************************************************
* KANSO INSTALLATION FILE
********************************************************************************/

/**
 * This is the default settings file for new Kanso installations.
 * By default, Kanso will delete this file once installation has been completed. 
 * If you are reading this file from your Kanso installation, then Kanso is NOT installed yet.
 * Note this file must be named as "Install.php" for Kanso to read it.
 */

return 
[

/********************************************************************************
* KANSO DATABSE SETTINGS
********************************************************************************/

/**
 * These settings allow Kanso to connect to your MySQL database. 
 * The settings are set to run on a local machine by default.
 */
'host' 	   => 'localhost',
'user'	   => 'root',
'password' => 'root',
'dbname'   => 'Kanso',

/********************************************************************************
* KANSO DEFAULT AUTHOR
********************************************************************************/

/**
 * Default author configuration.
 */
'KANSO_AUTHOR_USERNAME' => 'admin',
'KANSO_AUTHOR_EMAIL'    => 'admin@example.com',
'KANSO_AUTHOR_PASSWORD' => 'password1',

/********************************************************************************
* KANSO DEFAULT CONFIGURATION
********************************************************************************/

/* Kanso run mode */
'KANSO_RUN_MODE' => 'CMS',

/* Default theme */
'KANSO_THEME_NAME' => 'Roshi',

/* Default site title */
'KANSO_SITE_TITLE' => 'Kanso',

/* Default site title */
'KANSO_SITE_DESCRIPTION' => 'Kanso is a lightweight CMS written in PHP with a focus on simplicity, usability and of course writing.',

/* Default sitemap route */
'KANSO_SITEMAP' => 'sitemap.xml',

/* Default permalinks */
'KANSO_PERMALINKS' => 'year/month/postname/',

/* Default permalinks route */
'KANSO_PERMALINKS_ROUTE' => '(:year)/(:month)/(:postname)/',

/* Posts per page */
'KANSO_POSTS_PER_PAGE' => 10,

/* Route tags */
'KANSO_ROUTE_TAGS' => true,

/* Route categories */
'KANSO_ROUTE_CATEGORIES' => true,

/* Route authors */
'KANSO_ROUTE_AUTHORS' => true,

/* Thumbnail sizes */
'KANSO_THUMBNAILS' => [400, 800, 1200],

/* Image quality */
'KANSO_IMG_QUALITY' => 80,

/* Use CDN */
'KANSO_USE_CDN' => false,

/* CDN url */
'KASNO_CDN_URL' => '', 

/* Use cache */
'KANSO_USE_CACHE' => false,

/* Cache life */
'KANSO_CACHE_LIFE' => '', 

/* Enable comments */
'KANSO_COMMENTS_OPEN' => true,

/* Static pages list */
'KANSO_STATIC_PAGES' => [],

/* Static author pages */
'KANSO_AUTHOR_SLUGS' => ['john-appleseed'],

];

?>


