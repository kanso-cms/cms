<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application pages routes
 *
 * @author Joe J. Howard
 */

# Static pages
$staticPages = $SQL->SELECT('slug')->FROM('posts')->WHERE('type', '=', 'page')->FIND_ALL();

foreach ($staticPages as $page)
{
	$slug = $page['slug'];
	$router->get("/$slug/", '\kanso\cms\application\Application::applyRoute', 'page');
	$router->get("/$slug/feed/", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rss", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/atom", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rdf", '\kanso\cms\application\Application::loadRssFeed', 'page');
}