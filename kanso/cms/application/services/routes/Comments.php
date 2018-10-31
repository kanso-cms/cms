<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application comments routes.
 *
 * @author Joe J. Howard
 */

// Ajax Post Comments
if ($config->get('cms.enable_comments') === true)
{
	$router->post('/comments/', '\app\controllers\Comments@addComment', '\app\models\Comments');
}
