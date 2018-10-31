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
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route') . 'feed/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix . '/' . $config->get('cms.permalinks_route'), '\kanso\cms\application\Application::applyRoute', 'single');
