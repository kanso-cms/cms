<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application search routes.
 *
 * @author Joe J. Howard
 */
$router->get('/search-results/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Search');
$router->get('/search-results/page/(:num)/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Search');
