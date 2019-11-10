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
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rss/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/atom/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rdf/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/', '\kanso\cms\query\Dispatcher@loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route'), '\kanso\cms\query\Dispatcher@applyRoute', 'single');
