<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\controllers;

use kanso\cms\admin\controllers\Controller;

/**
 * Admin panel account pages controller
 *
 * @author Joe J. Howard
 */
class Accounts extends Controller
{
	/**
     * {@inheritdoc}
     */
	protected function getModelClass(): string
	{
		return '\kanso\cms\admin\models\Accounts';
	}
}
