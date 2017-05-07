<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin\Controllers;

use Kanso\CMS\Admin\Controllers\Controller;

/**
 * Admin panel dashboard pages controller
 *
 * @author Joe J. Howard
 */
class Dashboard extends Controller
{
	/**
     * {@inheritdoc}
     */
	protected function getModelClass()
	{
		if ($this->requestName === 'articles')
		{
			return '\Kanso\CMS\Admin\Models\Articles';
		}
		else if ($this->requestName === 'pages')
		{
			return '\Kanso\CMS\Admin\Models\Pages';
		}
		else if ($this->requestName === 'tags')
		{
			return '\Kanso\CMS\Admin\Models\Tags';
		}
		else if ($this->requestName === 'categories')
		{
			return '\Kanso\CMS\Admin\Models\Categories';
		}
		else if ($this->requestName === 'comments')
		{
			return '\Kanso\CMS\Admin\Models\Comments';
		}
		else if ($this->requestName === 'commentUsers')
		{
			return '\Kanso\CMS\Admin\Models\commentUsers';
		}
		else if ($this->requestName === 'mediaLibrary')
		{
			return '\Kanso\CMS\Admin\Models\MediaLibrary';
		}
		else if ($this->requestName === 'writer')
		{
			return '\Kanso\CMS\Admin\Models\Writer';
		}
		else if (in_array($this->requestName, ['settings', 'settingsAccount', 'settingsAuthor', 'settingsKanso', 'settingsUsers', 'settingsTools']))
		{
			return '\Kanso\CMS\Admin\Models\Settings';
		}

		return false;
	}
}
