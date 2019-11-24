<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\sitemap;

use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

/**
 * Sitemap builder.
 *
 * @author Joe J. Howard
 */
class SiteMap
{
	/**
	 * Request object.
	 *
	 * @var \kanso\framework\http\request\Request
	 */
	private $request;

	/**
	 * Response object.
	 *
	 * @var \kanso\framework\http\response\Response
	 */
	private $response;

	/**
	 * Route tags.
	 *
	 * @var bool
	 */
	private $routeTags;

	/**
	 * Route categories.
	 *
	 * @var bool
	 */
	private $routeCategories;

	/**
	 * Route authors.
	 *
	 * @var bool
	 */
	private $routeAuthors;

	/**
	 * Route authors.
	 *
	 * @var bool
	 */
	private $routeAttachements;

	/**
	 * Array of custom post type routes.
	 *
	 * @var array
	 */
	private $customPostTypes;

    /**
     * Constructor.
     *
     * @param \kanso\framework\http\request\Request   $request  Request object
     * @param \kanso\framework\http\response\Response $response Response object
     */
    public function __construct(Request $request, Response $response, bool $routeTags, bool $routeCategories, bool $routeAuthors, bool $routeAttachements, array $customPostTypes = [])
    {
        $this->request = $request;

        $this->response = $response;

        $this->routeTags = $routeTags;

        $this->routeCategories = $routeCategories;

        $this->routeAuthors = $routeAuthors;

        $this->routeAttachements = $routeAttachements;

        $this->customPostTypes = $customPostTypes;
    }

    /**
     * Outputs the sitemap to the response.
     */
    public function display(): void
    {
		// Set appropriate content type header
        $this->response->format()->set('xml');

        // Set the response body
        $this->response->body()->set($this->build());

        // Set the status
        $this->response->status()->set(200);

        // Disable the cache
        $this->response->disableCaching();
    }

	/**
	 * Returns the sitemap XML.
	 *
	 * @return string
	 */
	private function build(): string
	{
		$XML = $this->response->view()->display($this->template('head'));

		$XML .= $this->response->view()->display($this->template('pages'));

		if ($this->routeTags)
		{
            $XML .= $this->response->view()->display($this->template('tags'));
        }

        if ($this->routeCategories)
		{
            $XML .= $this->response->view()->display($this->template('categories'));
        }

        if ($this->routeAuthors)
		{
            $XML .= $this->response->view()->display($this->template('authors'));
        }

		$XML .= $this->response->view()->display($this->template('posts'));

		foreach ($this->customPostTypes as $type => $route)
    	{
    		$XML .= $this->response->view()->display($this->template('custom-posts'), ['type' => $type]);
    	}

    	if ($this->routeAttachements)
		{
            $XML .= $this->response->view()->display($this->template('attachments'));
        }

		$XML .= $this->response->view()->display($this->template('footer'));

		return $XML;
	}

	/**
	 * Load an RSS template file.
	 *
	 * @param  string $name The name of the template to load
	 * @return string
	 */
	private function template(string $name): string
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $name . '.php';
	}
}
