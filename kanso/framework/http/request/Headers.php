<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

/**
 * Request headers class.
 *
 * @author Joe J. Howard
 */
class Headers
{
    /**
     * Array access.
     *
     * @var string
     */
    protected $data = [];

    /**
     * Acceptable content types.
     *
     * @var array
     */
    protected $acceptableContentTypes;

    /**
     * Acceptable languages.
     *
     * @var array
     */
    protected $acceptableLanguages;

    /**
     * Acceptable character sets.
     *
     * @var array
     */
    protected $acceptableCharsets;

    /**
     * Acceptable encodings.
     *
     * @var array
     */
    protected $acceptableEncodings;

    /**
     * Special-case HTTP headers that are otherwise unidentifiable as HTTP headers.
     * Typically, HTTP headers in the $_SERVER array will be prefixed with
     * `HTTP_` or `X_`. These are not so we list them here for later reference.
     *
     * @var array
     */
    private $special =
    [
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE',
        'X-PJAX',
    ];

    /**
     * Constructor. Loads the properties internally.
     *
     * @access public
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function __construct(array $server = [])
    {
        $this->data = $this->extract($server);
    }

    /**
     * Reload the headers.
     *
     * @access public
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function reload(array $server = [])
    {
        $this->data = $this->extract($server);
    }

    /**
     * Returns a fresh copy of the headers.
     *
     * @access private
     * @param  array $server Optional server overrides (optional) (default [])
     * @return array
     */
    private function extract($server): array
    {
        $results = [];

        $server = empty($server) ? $_SERVER : $server;

        // Loop through the $_SERVER superglobal and save result consistently
        foreach ($server as $key => $value)
        {
            $key = strtoupper($key);

            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || [$key, $this->special])
            {
                if ($key === 'HTTP_CONTENT_LENGTH')
                {
                    continue;
                }

                $results[$this->normalizeKey($key)] = $value;
            }
        }
        return $results;
    }

    /**
     * Normalizes header names.
     *
     * @param  string $name Header name
     * @return string
     */
    protected function normalizeKey(string $name): string
    {
        return strtoupper(str_replace('-', '_', $name));
    }

    /**
     * Parses a accpet header and returns the values in descending order of preference.
     *
     * @param  string|null $headerValue Header value
     * @return array
     */
    protected function parseAcceptHeader(string $headerValue = null): array
    {
        $groupedAccepts = [];

        if(empty($headerValue))
        {
            return $groupedAccepts;
        }

        // Collect acceptable values
        foreach(explode(',', $headerValue) as $accept)
        {
            $quality = 1;
            if(strpos($accept, ';'))
            {
                // We have a quality so we need to split some more
                list($accept, $quality) = explode(';', $accept, 2);
                // Strip the "q=" part so that we're left with only the numeric value
                $quality = substr(trim($quality), 2);
            }
            $groupedAccepts[$quality][] = trim($accept);
        }
        // Sort in descending order of preference
        krsort($groupedAccepts);
        // Flatten array and return it
        return array_merge(...array_values($groupedAccepts));
    }

    /**
     * Returns an array of acceptable content types in descending order of preference.
     *
     * @param  string|null $default Default content type
     * @return array
     */
    public function acceptableContentTypes(string $default = null): array
    {
        if (!isset($this->acceptableContentTypes) && isset($this->data['HTTP_ACCEPT']))
        {
            $this->acceptableContentTypes = $this->parseAcceptHeader($this->data['HTTP_ACCEPT']);
        }

        return $this->acceptableContentTypes ?: (array) $default;
    }

    /**
     * Returns an array of acceptable content types in descending order of preference.
     *
     * @param  string|null $default Default language
     * @return array
     */
    public function acceptableLanguages(string $default = null): array
    {
        if(!isset($this->acceptableLanguages) && isset($this->data['HTTP_ACCEPT_LANGUAGE']))
        {
            $this->acceptableLanguages = $this->parseAcceptHeader($this->data['HTTP_ACCEPT_LANGUAGE']);
        }

        return $this->acceptableLanguages ?: (array) $default;
    }

    /**
     * Returns an array of acceptable content types in descending order of preference.
     *
     * @param  string|null $default Default charset
     * @return array
     */
    public function acceptableCharsets(string $default = null): array
    {
        if(!isset($this->acceptableCharsets) && isset($this->data['HTTP_ACCEPT_CHARSET']))
        {
            $this->acceptableCharsets = $this->parseAcceptHeader($this->data['HTTP_ACCEPT_CHARSET']);
        }

        return $this->acceptableCharsets ?: (array) $default;
    }

    /**
     * Returns an array of acceptable content types in descending order of preference.
     *
     * @param  string|null $default Default encoding
     * @return array
     */
    public function acceptableEncodings(string $default = null): array
    {
        if(!isset($this->acceptableEncodings) && isset($this->data['HTTP_ACCEPT_ENCODING']))
        {
            $this->acceptableEncodings = $this->parseAcceptHeader($this->data['HTTP_ACCEPT_ENCODING']);
        }

        return $this->acceptableEncodings ?: (array) $default;
    }

    /**
     * Return all properties.
     *
     * @access public
     * @return array
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * Get a property by key.
     *
     * @access public
     * @return string|null
     */
    public function __get(string $key)
    {
        if (isset($this->data[$this->normalizeKey($key)]))
        {
            return $this->data[$this->normalizeKey($key)];
        }

        return null;
    }

    /**
     * Set a property by key.
     *
     * @access public
     */
    public function __set(string $key, $value)
    {
        $this->data[$this->normalizeKey($key)] = $value;
    }

    /**
     * Check if a property by key exists.
     *
     * @access public
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$this->normalizeKey($key)]);
    }

    /**
     * Unset a property by key.
     *
     * @access public
     */
    public function __unset(string $key)
    {
        if (isset($this->data[$this->normalizeKey($key)]))
        {
            unset($this->data[$this->normalizeKey($key)]);
        }
    }
}
