<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application pages routes.
 *
 * @author Joe J. Howard
 */

// Static pages
$staticPages = $SQL->SELECT('slug')->FROM('posts')->WHERE('type', '=', 'page')->FIND_ALL();

foreach ($staticPages as $page)
{
	$slug = trim($page['slug'], '/');
	$router->get("/$slug/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Page');
	$router->get("/$slug/feed/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Page');
	$router->get("/$slug/feed/rss", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Page');
	$router->get("/$slug/feed/atom", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Page');
	$router->get("/$slug/feed/rdf", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Page');
}
