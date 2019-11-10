<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application home routes.
 *
 * @author Joe J. Howard
 */

// Homepage
$router->get('/', '\kanso\cms\query\Dispatcher@applyRoute', 'home');
$router->get('/page/(:num)/', '\kanso\cms\query\Dispatcher@applyRoute', 'home');
$router->get('/feed/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'home');
$router->get('/feed/rss/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'home');
$router->get('/feed/atom/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'home');
$router->get('/feed/rdf/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'home');

// Blog Homepage
if (!empty($blogPrefix))
{
	$router->get("$blogPrefix/", '\kanso\cms\query\Dispatcher@applyRoute', 'home-page');
	$router->get("$blogPrefix/page/(:num)/", '\kanso\cms\query\Dispatcher@applyRoute', 'home-page');
	$router->get("$blogPrefix/feed/", '\kanso\cms\query\Dispatcher@loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/rss/", '\kanso\cms\query\Dispatcher@loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/atom/", '\kanso\cms\query\Dispatcher@loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/rdf/", '\kanso\cms\query\Dispatcher@loadRssFeed', 'home-page');
}
