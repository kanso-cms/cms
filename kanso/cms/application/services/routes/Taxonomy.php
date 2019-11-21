<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application taxonomy routes.
 *
 * @author Joe J. Howard
 */

// Category
if ($config->get('cms.route_categories') === true)
{

	$router->get("$blogPrefix/category/(:all)/feed/rss/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Category');
	$router->get("$blogPrefix/category/(:all)/feed/atom/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Category');
	$router->get("$blogPrefix/category/(:all)/feed/rdf/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Category');
	$router->get("$blogPrefix/category/(:all)/page/(:num)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Category');
	$router->get("$blogPrefix/category/(:all)/feed/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Category');
	$router->get("$blogPrefix/category/(:all)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Category');
}

// Tag
if ($config->get('cms.route_tags') === true)
{
	$router->get("$blogPrefix/tag/(:any)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Tag');
	$router->get("$blogPrefix/tag/(:any)/page/(:num)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Tag');
	$router->get("$blogPrefix/tag/(:any)/feed/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rss/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Tag');
	$router->get("$blogPrefix/tag/(:any)/feed/atom/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rdf/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Tag');
}

// Author
if ($config->get('cms.route_authors') === true)
{
	$authorSlugs = $SQL->SELECT('slug')->FROM('users')->WHERE('role', '=', 'administrator')->OR_WHERE('role', '=', 'writer')->FIND_ALL();

	foreach ($authorSlugs as $slug)
	{
		$slug = $slug['slug'];
		$router->get("$blogPrefix/author/$slug/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Author');
		$router->get("$blogPrefix/author/$slug/page/(:num)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Author');
		$router->get("$blogPrefix/author/$slug/feed/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Author');
		$router->get("$blogPrefix/author/$slug/feed/rss/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Author');
		$router->get("$blogPrefix/author/$slug/feed/atom/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Author');
		$router->get("$blogPrefix/author/$slug/feed/rdf/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Author');
	}
}
