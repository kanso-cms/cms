<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application attachment routes
 *
 * @author Joe J. Howard
 */

# Attachments
if ($config->get('cms.route_attachments') === true)
{
	$router->get("$blogPrefix/attachment/(:any)/", '\kanso\cms\application\Application::applyRoute', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
}