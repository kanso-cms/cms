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
$router->get('/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/page/(:num)/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/feed/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'home');

// Blog Homepage
if (!empty($blogPrefix))
{
	$router->get("$blogPrefix/", '\kanso\cms\application\Application::applyRoute', 'home-page');
	$router->get("$blogPrefix/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'home-page');
	$router->get("$blogPrefix/feed/", '\kanso\cms\application\Application::loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'home-page');
	$router->get("$blogPrefix/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'home-page');
}
