<?php
namespace Kanso\Rss;

/**
 * This class is used to XML/RSS feeds for the homepage and articles
 *
 * When called it will build a valid RSS feed depending on the request
 *
 */
class Rss
{
	/**
	 * @var string    RSS feed format
	 */
	private $format;

	/**
	 * @var string    headers for feed formats
	 */
	private $headers = [
		'atom' => 'application/atom+xml',
		'rss'  => 'application/rss+xml',
		'rdf'  => 'application/rdf+xm',
	];

	/**
	 * Constructor
	 *
	 * @param string    $format    rss|rdf|atom
	 *
	 */
	public function __construct($format = 'rss')
	{
		if ($format === 'feed') $format = 'rss';
		$this->format = $format;
	}

	/**
	 * Render the XML into the response HTPP response
	 *
	 */
	public function render()
	{
		# Set the content type to XML
		\Kanso\Kanso::getInstance()->Response->setheaders(['Content-Type' => $this->headers[$this->format]]);

		\Kanso\Kanso::getInstance()->Response->setBody($this->xml());
	}

	/**
	 * Load an RSS XML feed
	 * 
	 * @return string
	 *
	 */
	private function xml()
	{
		$query = \Kanso\Kanso::getInstance()->Query;
		$this->template('head');
		$this->template('posts');
		$this->template('footer');
		return \Kanso\Kanso::getInstance()->View->display();
	}

	/**
	 * Load an RSS template file
	 * 
	 * @param  string    $name    name of the file
	 * @return string
	 */
	private function template($name)
	{
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.strtolower($this->format).DIRECTORY_SEPARATOR.$name.'.php';
		return \Kanso\Kanso::getInstance()->View->template($path);
	}
}