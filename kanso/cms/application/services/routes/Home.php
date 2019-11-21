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
$router->get('/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Home');
$router->get('/page/(:num)/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Home');
$router->get('/feed/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Home');
$router->get('/feed/rss/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Home');
$router->get('/feed/atom/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Home');
$router->get('/feed/rdf/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Home');

// Blog Homepage
if (!empty($blogPrefix))
{
	$router->get("$blogPrefix/", '\kanso\cms\query\controllers\Content@apply', 'kanso\cms\query\models\HomePage');
	$router->get("$blogPrefix/page/(:num)/", '\kanso\cms\query\controllers\Content@apply', 'kanso\cms\query\models\HomePage');
	$router->get("$blogPrefix/feed/", '\kanso\cms\query\controllers\Rss@load', 'kanso\cms\query\models\HomePage');
	$router->get("$blogPrefix/feed/rss/", '\kanso\cms\query\controllers\Rss@load', 'kanso\cms\query\models\HomePage');
	$router->get("$blogPrefix/feed/atom/", '\kanso\cms\query\controllers\Rss@load', 'kanso\cms\query\models\HomePage');
	$router->get("$blogPrefix/feed/rdf/", '\kanso\cms\query\controllers\Rss@load', 'kanso\cms\query\models\HomePage');
}
