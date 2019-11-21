<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\controllers;

use kanso\cms\sitemap\SiteMap as XMLMap;
use kanso\framework\mvc\controller\Controller;

/**
 * CMS Query Dispatcher.
 *
 * @author Joe J. Howard
 */
class Sitemap extends Controller
{
    /**
     * Loads the sitemap route.
     */
    public function load(): void
    {
    	$this->model->filter();

    	$template = $this->Config->get('cms.themes_path') . '/' . $this->Config->get('cms.theme_name') . '/sitemap.php';

	    if ($this->Filesystem->exists($template))
		{
			$this->fileResponse($template);
		}
		else
		{
			$sitemap = new XMLMap(
				$this->Request,
				$this->Response,
				$this->Config->get('cms.route_tags'),
				$this->Config->get('cms.route_categories'),
				$this->Config->get('cms.route_authors'),
				$this->Config->get('cms.route_attachments'),
				$this->Config->get('cms.custom_posts')
			);

			$sitemap->display();
		}
	}
}
