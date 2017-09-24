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
	# Base category
	$router->get("$blogPrefix/category/(:any)/", '\kanso\cms\application\Application::applyRoute', 'category1');
	$router->get("$blogPrefix/category/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'category1');
	$router->get("$blogPrefix/category/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'category1');
	$router->get("$blogPrefix/category/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'category1');
	$router->get("$blogPrefix/category/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'category1');
	$router->get("$blogPrefix/category/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'category1');

	# Parent/Child category
	$router->get("$blogPrefix/category/(:any)/(:any)/", '\kanso\cms\application\Application::applyRoute', 'category2');
	$router->get("$blogPrefix/category/(:any)/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'category2');
	$router->get("$blogPrefix/category/(:any)/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'category2');
	$router->get("$blogPrefix/category/(:any)/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'category2');
	$router->get("$blogPrefix/category/(:any)/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'category2');
	$router->get("$blogPrefix/category/(:any)/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'category2');

	# Parent/Child category
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/", '\kanso\cms\application\Application::applyRoute', 'category3');
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'category3');
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'category3');
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'category3');
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'category3');
	$router->get("$blogPrefix/category/(:any)/(:any)/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'category3');
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
