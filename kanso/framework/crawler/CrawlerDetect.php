<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\crawler;

use kanso\framework\crawler\fixtures\Exclusions;
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
     * Exclusions object.
     *
     * @var \kanso\framework\crawler\fixtures\Exclusions
     */
    private $exclusions;

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
     * The compiled regex string.
     *
     * @var string
     */
    private $compiledRegex;

    /**
     * The compiled exclusions regex string.
     *
     * @var string
     */
    private $compiledExclusions;

    /**
     * Class constructor.
     *
     * @param \kanso\framework\http\request\Headers        $headers    HTTP request headers object
     * @param \kanso\framework\crawler\fixtures\Inclusions $inclusions Crawler inclusions
     * @param \kanso\framework\crawler\fixtures\Exclusions $exclusions Crawler exclusions
     */
    public function __construct(Headers $headers, Inclusions $inclusions, Exclusions $exclusions)
    {
        $this->headers = $headers;

        $this->inclusions = $inclusions;

        $this->exclusions = $exclusions;

        $this->compiledRegex = $this->compileRegex($this->inclusions->asArray());

        $this->compiledExclusions = $this->compileRegex($this->exclusions->asArray());

        $this->userAgent = $this->headers->HTTP_USER_AGENT;
    }

    /**
     * Compile the regex patterns into one regex string.
     *
     * @param  array  $patterns Definition patterns
     * @return string
     */
    public function compileRegex(array $patterns): string
    {
        return '(' . implode('|', $patterns) . ')';
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

        $agent = preg_replace('/' . $this->compiledExclusions . '/i', '', $agent);

        if (strlen(trim($agent)) == 0)
        {
            return false;
        }

        $result = preg_match('/' . $this->compiledRegex . '/i', trim($agent), $matches);

        if ($matches)
        {
            $this->matches = $matches;
        }

        return (bool) $result;
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
