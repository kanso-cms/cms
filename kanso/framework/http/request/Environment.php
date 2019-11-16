<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

use kanso\framework\common\MagicArrayAccessTrait;

/**
 * Environment aware class.
 *
 * @author Joe J. Howard
 */
class Environment
{
    use MagicArrayAccessTrait;

    /**
     * $_SERVER.
     *
     * @var array
     */
    private $server;

    /**
     * Constructor. Loads the properties internally.
     *
     * @access public
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function __construct(array $server = [])
    {
        $this->server = empty($server) ? $_SERVER : $server;

        $this->data = $this->extract();
    }

    /**
     * Reload the environment properties.
     *
     * @access public
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function reload(array $server  = [])
    {
         $this->server = empty($server) ? $_SERVER : $server;

        $this->data = $this->extract();
    }

    /**
     * Returns a fresh copy of the environment properties.
     *
     * @access private
     * @return array
     */
    private function extract(): array
    {
        return
        [
            'REQUEST_METHOD'     => $this->requestMethod(),
            'SCRIPT_NAME'        => $this->scriptName(),
            'SERVER_NAME'        => $this->serverName(),
            'SERVER_PORT'        => $this->serverPort(),
            'HTTP_PROTOCOL'      => $this->httpProtocol(),
            'DOCUMENT_ROOT'      => $this->documentRoot(),
            'HTTP_HOST'          => $this->httpHost(),
            'DOMAIN_NAME'        => $this->domainName(),
            'REQUEST_URI'        => $this->requestUri(),
            'REQUEST_PATH'       => $this->requestPath(),
            'REQUEST_URL'        => $this->requestUrl(),
            'QUERY_STRING'       => $this->queryString(),
            'REMOTE_ADDR'        => $this->remoteAddr(),
            'REFERER'            => $this->referer(),
            'HTTP_USER_AGENT'    => $this->httpUserAgent(),
            'REQUEST_TIME'       => $this->requestTime(),
            'REQUEST_TIME_FLOAT' => $this->requestTimeFloat(),
        ];
    }

    /**
     * Returns the REQUEST_METHOD.
     *
     * @access private
     * @return string
     */
    private function requestMethod(): string
    {
        return !isset($this->server['REQUEST_METHOD']) ? 'CLI' : $this->server['REQUEST_METHOD'];
    }

    /**
     * Returns the SCRIPT_NAME.
     *
     * @access private
     * @return string
     */
    private function scriptName(): string
    {
        if (isset($this->server['SCRIPT_NAME']) && !empty($this->server['SCRIPT_NAME']))
        {
            $scripts = explode('/', trim($this->server['SCRIPT_NAME'], '/'));
        }
        elseif (isset($this->server['PHP_SELF']) && !empty($this->server['PHP_SELF']))
        {
            $scripts = explode('/', trim(substr($this->server['PHP_SELF'], strrpos($this->server['PHP_SELF'], '/') + 1), '/'));
        }
        else
        {
            return '/index.php';
        }

        return '/' . array_pop($scripts);
    }

    /**
     * Returns the SERVER_NAME.
     *
     * @access private
     * @return string
     */
    private function serverName(): string
    {
        if (isset($this->server['SERVER_NAME']))
        {
            return $this->server['SERVER_NAME'];
        }
        elseif (isset($this->server['HTTP_HOST']))
        {
            if (strpos($this->server['HTTP_HOST'], ':') !== false)
            {
                $name = explode(':', $this->server['HTTP_HOST']);

                return trim($name[0]);
            }
            elseif (strpos($this->server['HTTP_HOST'], '.') !== false)
            {
                $name = explode('.', $this->server['HTTP_HOST']);

                return trim($name[0]);
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Returns the SERVER_PORT.
     *
     * @access private
     * @return int
     */
    private function serverPort(): int
    {
        return isset($this->server['SERVER_PORT']) ? intval($this->server['SERVER_PORT']) : 80;
    }

    /**
     * Returns the HTTP_PROTOCOL.
     *
     * @access private
     * @return string
     */
    private function httpProtocol(): string
    {
        if (isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] === 443)
        {
            return 'https';
        }
        elseif (isset($this->server['HTTPS']) && ($this->server['HTTPS'] === 1 || $this->server['HTTPS'] === 'on'))
        {
            return 'https';
        }

        return 'http';
    }

    /**
     * Returns the DOCUMENT_ROOT.
     *
     * @access private
     * @return string
     */
    private function documentRoot(): string
    {
        return isset($this->server['DOCUMENT_ROOT']) ? $this->server['DOCUMENT_ROOT'] : dirname(dirname(dirname(dirname(dirname(__FILE__)))));
    }

    /**
     * Returns the HTTP_HOST.
     *
     * @access private
     * @return string
     */
    private function httpHost(): string
    {
        if (isset($this->server['HTTP_HOST']))
        {
            return $this->httpProtocol() . '://' . str_replace(['http://', 'https://'], ['', ''], $this->server['HTTP_HOST']);
        }

        return '';
    }

    /**
     * Returns the DOMAIN_NAME.
     *
     * @access private
     * @return string
     */
    private function domainName(): string
    {
        return str_replace('www.', '', str_replace($this->httpProtocol() . '://', '', $this->httpHost()));
    }

    /**
     * Returns the REQUEST_URI.
     *
     * @access private
     * @return string
     */
    private function requestUri(): string
    {
        return isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : '/';
    }

    /**
     * Returns the REQUEST_URI without the query string.
     *
     * @access private
     * @return string
     */
    private function requestPath(): string
    {
        $uri = $this->requestUri();

        if (strpos($uri, '?') !== false)
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return ltrim(rtrim($uri, '/'), '/');
    }

    /**
     * Returns the REQUEST_URI.
     *
     * @access private
     * @return string
     */
    private function requestUrl(): string
    {
        return $this->httpHost() . $this->requestUri();
    }

    /**
     * Returns the QUERY_STRING.
     *
     * @access private
     * @return string
     */
    private function queryString(): string
    {
        $uri = $this->requestUri();

        return strpos($uri, '?') !== false ? substr($uri, strrpos($uri, '?') + 1) : '';
    }

    /**
     * Returns the REMOTE_ADDR.
     *
     * @access private
     * @return string
     */
    private function remoteAddr(): string
    {
        if (isset($this->server['HTTP_CLIENT_IP']))
        {
            $ipaddress = $this->server['HTTP_CLIENT_IP'];
        }
        elseif (isset($this->server['HTTP_X_FORWARDED_FOR']))
        {
            $ipaddress = $this->server['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($this->server['HTTP_X_FORWARDED']))
        {
            $ipaddress = $this->server['HTTP_X_FORWARDED'];
        }
        elseif (isset($this->server['HTTP_FORWARDED_FOR']))
        {
            $ipaddress = $this->server['HTTP_FORWARDED_FOR'];
        }
        elseif (isset($this->server['HTTP_FORWARDED']))
        {
            $ipaddress = $this->server['HTTP_FORWARDED'];
        }
        elseif (isset($this->server['REMOTE_ADDR']))
        {
            $ipaddress = $this->server['REMOTE_ADDR'];
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     * Returns the HTTP_REFERER.
     *
     * @access private
     * @return string
     */
    private function referer(): string
    {
        return isset($this->server['HTTP_REFERER']) ? $this->server['HTTP_REFERER'] : '';
    }

    /**
     * Returns the HTTP_USER_AGENT.
     *
     * @access private
     * @return string
     */
    private function httpUserAgent()
    {
        return isset($this->server['HTTP_USER_AGENT']) ? $this->server['HTTP_USER_AGENT'] : '';
    }

    /**
     * Returns the REQUEST_TIME.
     *
     * @access private
     * @return int
     */
    private function requestTime(): int
    {
       return isset($this->server['REQUEST_TIME']) ? $this->server['REQUEST_TIME'] : time();
    }

    /**
     * Returns the REQUEST_TIME_FLOAT.
     *
     * @access private
     * @return float
     */
    private function requestTimeFloat(): float
    {
        return isset($this->server['REQUEST_TIME_FLOAT']) ? $this->server['REQUEST_TIME_FLOAT'] : microtime(true);
    }
}
