<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

/**
 * CDN class.
 *
 * @author Joe J. Howard
 */
class CDN
{
	/**
	 * The HTTP host for the CDN.
	 *
	 * @var string
	 */
	private $cdnHost;

	/**
	 * The current HTTP host.
	 *
	 * @var string
	 */
	private $currHost;

    /**
     * The current HTTP host.
     *
     * @var bool
     */
    private $enabled = false;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string $currHost HTTP host of current server
	 * @param string $cdnHost  Http host of CDN
	 * @param bool   $enabled  Is the CDN enabled ? (optional) (default false)
	 */
	public function __construct(string $currHost, string $cdnHost, bool $enabled = false)
    {
        $this->currHost = rtrim($currHost, '/');

        $this->cdnHost = rtrim($cdnHost, '/');

        $this->enabled = $enabled;
    }

    /**
     * Disable CDN.
     *
     * @access public
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable CDN.
     *
     * @access public
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Is CDN enabled ?
     *
     * @access public
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Filter HTML via the CDN.
     *
     * @access public
     * @param  string $html HTML to filter
     * @return string
     */
    public function filter(string $html): string
    {
        if (!$this->enabled)
        {
            return $html;
        }

        // Store variables locally
        $currHost = $this->currHost;
    	$cdnHost  = $this->cdnHost;

        // Replace <img> tags
        $html = preg_replace_callback('/<img [^>]*src="([^"]+)"[^>]*/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);

            }

            return $matches[0];

        }, $html);

        // Replace favicions and style sheets
        $html = preg_replace_callback('/<link rel="(shortcut icon|icon|stylesheet|apple-touch-icon)".+href="([^"]+)"/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);
            }

            return $matches[0];

        }, $html);

        $html = preg_replace_callback('/<link href="([^"]+)".+rel="(shortcut icon|icon|stylesheet|apple-touch-icon)"/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);
            }

            return $matches[0];

        }, $html);

        // Replace JS scripts
        $html = preg_replace_callback('/<script.+src="([^"]+)"/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);
            }

            return $matches[0];

        }, $html);

        // Background URLS
		$html = preg_replace_callback('/background:(\s+|)url\(([^)]+)\)/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);
            }

            return $matches[0];

        }, $html);

        $html = preg_replace_callback('/background-image:(\s+|)url\(([^)]+)\)/', function($matches) use ($currHost, $cdnHost)
        {
            if (strpos($matches[0], $currHost) !== false)
            {
                return str_replace($currHost, $cdnHost, $matches[0]);
            }

            return $matches[0];

        }, $html);

		return $html;
    }
}
