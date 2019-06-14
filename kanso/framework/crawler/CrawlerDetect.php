<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\crawler;

use kanso\framework\crawler\fixtures\Inclusions;
use kanso\framework\http\request\Headers;

class CrawlerDetect
{
    /**
     * Headers that contain a user agent.
     *
     * @var \kanso\framework\http\request\Headers
     */
    private $headers;

    /**
     * Inclusions object.
     *
     * @var \kanso\framework\crawler\fixtures\Inclusions
     */
    private $inclusions;

    /**
     * Store regex matches.
     *
     * @var array
     */
    private $matches = [];

    /**
     * The user agent.
     *
     * @var null
     */
    private $userAgent;

    /**
     * Class constructor.
     *
     * @param \kanso\framework\http\request\Headers        $headers    HTTP request headers object
     * @param \kanso\framework\crawler\fixtures\Inclusions $inclusions Crawler inclusions
     */
    public function __construct(Headers $headers, Inclusions $inclusions)
    {
        $this->headers = $headers;

        $this->inclusions = $inclusions;

        $this->userAgent = $this->headers->HTTP_USER_AGENT;
    }

    /**
     * Check user agent string against the regex.
     *
     * @param string|null $userAgent
     *
     * @return bool
     */
    public function isCrawler($userAgent = null): bool
    {
        $agent = $userAgent ?: $this->userAgent;

        if (!$agent || !is_string($agent) || strlen(trim($agent)) == 0)
        {
            return false;
        }

        $agent = trim($agent);

        foreach ($this->inclusions->asArray() as $inclusion)
        {
            preg_match('/' . $inclusion . '/i', trim($agent), $matches);

            if ($matches)
            {
                $this->matches = $matches;

                return true;
            }
        }

        return false;
    }

    /**
     * Return the matches.
     *
     * @return string|null
     */
    public function getMatches()
    {
        return isset($this->matches[0]) ? $this->matches[0] : null;
    }
}
