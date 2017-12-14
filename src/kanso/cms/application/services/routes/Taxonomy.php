<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application taxonomy routes
 *
 * @author Joe J. Howard
 */

# Category
if ($config->get('cms.route_categories') === true)
{
	
	$router->get("$blogPrefix/category/(:all)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:all)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:all)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:all)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'category');
	$router->get("$blogPrefix/category/(:all)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:all)/", '\kanso\cms\application\Application::applyRoute', 'category');
}

# Tag
if ($config->get('cms.route_tags') === true)
{
	$router->get("$blogPrefix/tag/(:any)/", '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get("$blogPrefix/tag/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
}

# Author
if ($config->get('cms.route_authors') === true)
{
	$authorSlugs = $SQL->SELECT('slug')->FROM('users')->WHERE('role', '=', 'administrator')->OR_WHERE('role', '=', 'writer')->FIND_ALL();
	
	foreach ($authorSlugs as $slug)
	{
		$slug = $slug['slug'];
		$router->get("$blogPrefix/author/$slug/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("$blogPrefix/author/$slug/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("$blogPrefix/author/$slug/feed/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/author/$slug/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/author/$slug/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/author/$slug/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'author');
	}
}
