<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application attachment routes.
 *
 * @author Joe J. Howard
 */

// Attachments
if ($config->get('cms.route_attachments') === true)
{
	$router->get("$blogPrefix/attachment/(:any)/", '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rss/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/atom/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rdf/", '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Attachment');
}
