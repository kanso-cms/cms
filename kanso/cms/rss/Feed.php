<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\rss;

use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Mime;

/**
 * RSS feeds.
 *
 * @author Joe J. Howard
 */
class Feed
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
	 * RSS format to load.
	 *
	 * @var string
	 */
	private $format;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\http\request\Request   $request  Request object
     * @param \kanso\framework\http\response\Response $response Response object
     * @param string                                  $format   RSS format 'rss'||'atom'||'rdf' (optional) (default 'rss')
     */
    public function __construct(Request $request, Response $response, string $format = 'rss')
    {
    	$format = $format === 'feed' ? 'rss' : $format;

        $this->request = $request;

        $this->response = $response;

        $this->format = $format;
    }

	/**
	 * Render the XML into the HTPP response.
	 *
	 * @access public
	 */
	public function render()
	{
		// Set appropriate content type header
        $this->response->format()->set(Mime::fromExt($this->format) . ', application/xml');

        // Filter posts for feed
        \kanso\Kanso::instance()->Filters->apply('RSS', $this);

        // Set the response body
        $this->response->body()->set($this->xml());

        // Set the status
        $this->response->status()->set(200);

        // Disable the cache
        $this->response->cache()->disable();
	}

	/**
	 * Load an RSS XML feed.
	 *
	 * @access private
	 * @return string
	 */
	private function xml(): string
	{
		$XML = $this->response->view()->display($this->template('head'));

		$XML .= $this->response->view()->display($this->template('posts'));

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
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . strtolower($this->format) . DIRECTORY_SEPARATOR . $name . '.php';
	}
}
