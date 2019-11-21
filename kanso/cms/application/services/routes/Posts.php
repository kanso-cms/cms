<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application posts routes.
 *
 * @author Joe J. Howard
 */

// Posts
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rss/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/atom/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rdf/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route'), '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Single');
