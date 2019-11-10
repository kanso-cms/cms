<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application sitemap route.
 *
 * @author Joe J. Howard
 */

// Sitemap
$router->get('/' . $config->get('cms.sitemap_route'), '\kanso\cms\query\Dispatcher@loadSiteMap');
