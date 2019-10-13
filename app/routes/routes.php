<?php

/*
 * ---------------------------------------------------------
 * Example routes
 * ---------------------------------------------------------
 *
 * Examples that come bundled with the framework
 */

/*
 * Example Basic Routing
 *
 * Uncomment below to see how use the MVC architecture
 */
// $kanso->Router->get('/welcome', '\app\controllers\Example@welcome', '\app\models\Example');

/*
 * Example Custom Admin Page
 *
 * Uncomment below to see how to add a custom page to the Admin Panel.
 */
// $kanso->Admin->addPage('My Page', 'my-page', 'superpowers', '\app\models\ExampleAdminPage', APP_DIR.'/views/example-admin-page.php');

/*
 * Example Adding a custom post-type
 *
 * Uncomment below to see how to add a custom post-type to the Admin Panel.
 */
// $kanso->Admin->registerPostType('Product', 'product', 'shopping-cart', 'products/(:year)/(:month)/(:postname)/');

/*
 * ---------------------------------------------------------
 * Application routes
 * ---------------------------------------------------------
 *
 * Define your custom application routes here
 */

/**
 * Default CRON setup.
 */
require_once '_cron.php';

/**
 * Setup for Gmail SMTP XOAUTH.
 */
require_once '_smtp-xoauth.php';
