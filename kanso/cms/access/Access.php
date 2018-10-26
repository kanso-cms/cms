<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\access;

use kanso\framework\file\Filesystem;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\exceptions\ForbiddenException;
use kanso\framework\http\response\Response;

/**
 * Access/Security.
 *
 * @author Joe J. Howard
 */
class Access
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
	 * Filesystem object
	 *
	 * @var \kanso\framework\file\Filesystem
	 */
	private $filesystem;

	/**
	 * Path to robots.txt.
	 *
	 * @var string
	 */
	private $robotsPath;

	/**
	 * Is ip address blocking enabled ?
	 *
	 * @var bool
	 */
	private $ipBlockEnabled;

	/**
	 * Array of whitelisted ip addresses.
	 *
	 * @var array
	 */
	private $ipWhitelist;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\http\request\Request   $request     Request object
     * @param \kanso\framework\http\response\Response $response    Response object
     * @param \kanso\framework\file\Filesystem        $filesystem  Filesystem instancea
     * @param bool                                    $blockIps    Should we block all IP address except the whitelist (optional) (default false)
     * @param array                                   $ipWhitelist Array of whitelisted ip addresses (optional) (default [])
     */
    public function __construct(Request $request, Response $response, Filesystem $filesystem, $blockIps = false, $ipWhitelist = [])
    {
        $this->request = $request;

        $this->response = $response;

        $this->filesystem = $filesystem;

        $this->robotsPath = $request->environment()->DOCUMENT_ROOT . '/robots.txt';

        $this->ipBlockEnabled = $blockIps;

        $this->ipWhitelist = $ipWhitelist;
    }

	/**
	 * Is ip blocking enabled ?
	 *
	 * @access public
	 * @return bool
	 */
	public function ipBlockEnabled(): bool
	{
		return $this->ipBlockEnabled;
	}

	/**
	 * Is ip address allowed.
	 *
	 * @access public
	 * @return bool
	 */
	public function isIpAllowed(): bool
	{
		if (empty($this->ipWhitelist))
		{
			return true;
		}

		$ip = $this->request->environment()->REMOTE_ADDR;

		if (!empty($ip))
		{
			return in_array($ip, $this->ipWhitelist);
		}

		return false;
	}

	/**
	 * Block the current request.
	 *
	 * @access public
	 */
	public function block()
	{
		throw new ForbiddenException('Blocked IP Address. The CMS has IP address blocking enabled - blocked ip: "' . $this->request->environment()->REMOTE_ADDR . '" from access.');
	}

	/**
	 * Returns the default robots.txt file contents.
	 *
	 * @access public
	 * @return string
	 */
	public function defaultRobotsText(): string
	{
		return "User-agent: *\nDisallow:";
	}

	/**
	 * Returns the block all robots.txt file contents.
	 *
	 * @access public
	 * @return string
	 */
	public function blockAllRobotsText(): string
	{
		return "User-agent: *\nDisallow: /";
	}

	/**
	 * Save the robots.txt file.
	 *
	 * @access public
	 * @param string $content Content to put into the file
	 */
	public function saveRobots(string $content = '')
	{
		$this->filesystem->putContents($this->robotsPath, $content);
	}

	/**
	 * Save the robots.txt file.
	 *
	 * @access public
	 */
	public function deleteRobots()
	{
		if ($this->filesystem->exists($this->robotsPath))
		{
			$this->filesystem->delete($this->robotsPath);
		}
	}
}
