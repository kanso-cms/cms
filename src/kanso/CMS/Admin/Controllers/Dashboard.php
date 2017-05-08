<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\controllers;

use kanso\cms\admin\controllers\Controller;

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
			return '\kanso\cms\admin\models\Articles';
		}
		else if ($this->requestName === 'pages')
		{
			return '\kanso\cms\admin\models\Pages';
		}
		else if ($this->requestName === 'tags')
		{
			return '\kanso\cms\admin\models\Tags';
		}
		else if ($this->requestName === 'categories')
		{
			return '\kanso\cms\admin\models\Categories';
		}
		else if ($this->requestName === 'comments')
		{
			return '\kanso\cms\admin\models\Comments';
		}
		else if ($this->requestName === 'commentUsers')
		{
			return '\kanso\cms\admin\models\commentUsers';
		}
		else if ($this->requestName === 'mediaLibrary')
		{
			return '\kanso\cms\admin\models\MediaLibrary';
		}
		else if ($this->requestName === 'writer')
		{
			return '\kanso\cms\admin\models\Writer';
		}
		else if (in_array($this->requestName, ['settings', 'settingsAccount', 'settingsAuthor', 'settingsKanso', 'settingsUsers', 'settingsTools']))
		{
			return '\kanso\cms\admin\models\Settings';
		}

		return false;
	}
}
