<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\controllers;

use kanso\framework\mvc\controller\Controller;
use kanso\framework\utility\Str;

/**
 * CMS Query Dispatcher.
 *
 * @author Joe J. Howard
 */
class Content extends Controller
{
    /**
     * Apply route to filter posts and load theme templates.
     */
    public function apply(): void
    {
    	$requestType = $this->model->requestType();
    	$filter      = $this->model->filter();
    	$template    = $this->getTemplate($requestType);

		if ($filter && $template)
		{
			if ($requestType !== 'page' && $requestType !== 'single')
			{
				$this->Response->disableCaching();
			}

			// Set the_post so we're looking at the first item
	        if (isset($this->Query->posts[0]))
	        {
	            $this->Query->post = $this->Query->posts[0];
	        }

			$this->fileResponse($template);
		}
		else
		{
			$this->Query->reset();

			$this->nextMiddleware();
		}
    }

	/**
	 * Determine what template to use.
	 *
	 * @return string|false
	 */
	private function getTemplate(string $requestType)
	{
		$themeDir = $this->Config->get('cms.themes_path') . '/' . $this->Config->get('cms.theme_name');

		// Waterfall of pages
		$waterfall =  [];

		// Explode request url
		$urlParts = explode('/', $this->Request->environment()->REQUEST_PATH);

		// 404s never get a template
		if ($this->Response->status()->get() === 404)
		{
			return false;
		}

		if ($requestType === 'home')
		{
			$waterfall[] = 'homepage';
			$waterfall[] = 'index';
		}
		elseif ($requestType === 'home-page')
		{
			$waterfall[] = 'home-' . array_shift($urlParts);
			$waterfall[] = 'blog';
			$waterfall[] = 'index';
		}
		elseif ($requestType === 'page')
		{
			$waterfall[] = 'page-' . array_shift($urlParts);
			$waterfall[] = 'page';
		}
		elseif (Str::getBeforeFirstChar($requestType, '-') === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);

			if ($this->Query->have_posts())
			{
				$waterfall[] = 'single-' . $this->Query->the_post_type();
			}

			$waterfall[] = 'single';
		}
		elseif ($requestType === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);
			$waterfall[] = 'single';
		}
		elseif ($requestType === 'tag')
		{
			if ($this->Query->the_taxonomy())
			{
				$waterfall[] = 'tag-' . $this->Query->the_taxonomy()->slug;
			}

			$waterfall[] = 'taxonomy-tag';
			$waterfall[] = 'tag';
			$waterfall[] = 'taxonomy';
		}
		elseif ($requestType === 'category')
		{
			if ($this->Query->the_taxonomy())
			{
				$waterfall[] = 'category-' . $this->Query->the_taxonomy()->slug;
			}

			$waterfall[] = 'taxonomy-category';
			$waterfall[] = 'category';
			$waterfall[] = 'taxonomy';
		}
		elseif ($requestType === 'author')
		{
			if ($this->Query->the_taxonomy())
			{
				$waterfall[] = 'author-' . $this->Query->the_taxonomy()->slug;
			}
			$waterfall[] = 'taxonomy-author';
			$waterfall[] = 'author';
			$waterfall[] = 'taxonomy';
		}
		elseif ($requestType === 'search')
		{
			$waterfall[] = 'search';
			$waterfall[] = 'index';
		}
		elseif ($requestType === 'attachment')
		{
			$waterfall[] = 'attachment-' . array_pop($urlParts);
			$waterfall[] = 'attachment';
		}
		elseif ($requestType === 'products')
		{
			$waterfall[] = 'page-products';
			$waterfall[] = 'products';
			$waterfall[] = 'index';
		}

		foreach ($waterfall as $name)
		{
			$template = "{$themeDir}/$name.php";

			if (file_exists($template))
			{
				return $template;
			}
		}

		if ($requestType === 'attachment')
		{
			$template = APP_DIR . '/views/attachment.php';

			if (file_exists($template))
			{
				return $template;
			}
		}

		if (file_exists("{$themeDir}/$requestType.php"))
		{
			return "{$themeDir}/$requestType.php";
		}

		return false;
	}
}
