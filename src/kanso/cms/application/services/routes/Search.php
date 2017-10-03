<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application search routes
 *
 * @author Joe J. Howard
 */

# Search
$router->get('/search-results/', '\kanso\cms\application\Application::applyRoute', 'search');
