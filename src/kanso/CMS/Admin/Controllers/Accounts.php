<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin\Controllers;

use Kanso\CMS\Admin\Controllers\Controller;

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
		return '\Kanso\CMS\Admin\Models\Accounts';
	}
}
