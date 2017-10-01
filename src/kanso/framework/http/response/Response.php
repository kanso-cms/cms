<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\http\response\Format;
use kanso\framework\http\response\Body;
use kanso\framework\http\response\Status;
use kanso\framework\http\response\Headers;
use kanso\framework\http\response\Cache;
use kanso\framework\http\cookie\Cookie;
use kanso\framework\http\session\Session;
use kanso\framework\http\response\Protocol;
use kanso\framework\mvc\view\View;
use kanso\framework\http\response\exceptions\NotFoundException;
use \kanso\framework\http\response\exceptions\ForbiddenException;
use \kanso\framework\http\response\exceptions\InvalidTokenException;
use \kanso\framework\http\response\exceptions\MethodNotAllowedException;
use kanso\framework\http\response\exceptions\Stop;

/**
 * HTTP Response manager
 *
 * @author Joe J. Howard
 */
class Response
{
    /**
     * The HTTP protocol
     *
     * @var \kanso\framework\http\response\Protocol
     */
    private $protocol;

    /**
     * The HTTP format
     *
     * @var \kanso\framework\http\response\Format
     */
    private $format;

    /**
     * The HTTP headers
     *
     * @var \kanso\framework\http\response\Headers
     */
    private $headers;

    /**
     * Cookie manager
     *
     * @var \kanso\framework\http\cookie\Cookie
     */
    private $cookie;

    /**
     * Response body
     *
     * @var \kanso\framework\http\response\Body
     */
    private $body;

    /**
     * Response status
     *
     * @var \kanso\framework\http\response\Status
     */
    private $status;

    /**
     * Response cache
     *
     * @var \kanso\framework\http\response\Cache
     */
    private $cache;

    /**
     * CDN manager
     *
     * @var \kanso\framework\http\response\CDN
     */
     private $CDN;

    /**
     * View renderer
     *
     * @var \kanso\framework\mvc\view\View
     */
     private $view;

    /**
     * Has the response been sent ?
     *
     * @var bool
     */
    private $sent = false;
    
    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\http\response\Protocol $protocol 
     * @param  \kanso\framework\http\response\Format   $format
     * @param  \kanso\framework\http\response\Body     $body
     * @param  \kanso\framework\http\response\Status   $status     
     * @param  \kanso\framework\http\response\Headers  $headers
     * @param  \kanso\framework\http\session\Session   $session
     * @param  \kanso\framework\http\response\Cache    $cache
     * @param  \kanso\framework\http\response\CDN      $CDN
     * @param  \kanso\framework\mvc\view\View          $view
     */
    public function __construct(Protocol $protocol, Format $format, Body $body, Status $status, Headers $headers, Cookie $cookie, Session $session, Cache $cache, CDN $CDN, View $view)
    {
        $this->format = $format;

        $this->body = $body;

        $this->status = $status;

        $this->headers = $headers;

        $this->cookie = $cookie;

        $this->cache = $cache;

        $this->session = $session;

        $this->protocol = $protocol;

        $this->CDN = $CDN;

        $this->view = $view;

        $this->format->set('text/html');
        
        $this->format->setEncoding('utf-8');
    }

    /**
     * Get the protocol object
     *
     * @access public
     * @return \kanso\framework\http\response\Protocol
     */
    public function protocol()
    {
        return $this->protocol();
    }

    /**
     * Get the body object
     *
     * @access public
     * @return \kanso\framework\http\response\Body
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Get the format object
     *
     * @access public
     * @return \kanso\framework\http\response\Format
     */
    public function format()
    {
        return $this->format;
    }

    /**
     * Get the status object
     *
     * @access public
     * @return \kanso\framework\http\response\Status
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the headers object
     *
     * @access public
     * @return \kanso\framework\http\response\Headers
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the cache object
     *
     * @access public
     * @return \kanso\framework\http\response\Cache
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * Get the cookie manager
     *
     * @access public
     * @return \kanso\framework\http\cookie\Cookie
     */
    public function cookie()
    {
        return $this->cookie;
    }

    /**
     * Get the session manager
     *
     * @access public
     * @return \kanso\framework\http\session\Session
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Get the CDN object
     *
     * @access public
     * @return \kanso\framework\http\response\Cache
     */
    public function CDN()
    {
        return $this->CDN;
    }

    /**
     * Get the view object
     *
     * @access public
     * @return \kanso\framework\mvc\view\View
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * Finalize all objects before sending the response
     *
     * @access public
     */
    private function finalize()
    {
        $this->headers->set('HTTP', $this->status->get().' '.$this->status->message());

        $this->headers->set('Content-length', $this->body->length());

        $this->headers->set('Content-Type', $this->format->get().';'.$this->format->getEncoding());

        if ($this->status->isRedirect() || $this->status->isEmpty())
        {
            $this->headers->remove('Content-Type');

            $this->headers->remove('Content-Length');

            $this->body->clear();
        }
        
        $this->finalizeBody();
    }

    /**
     * Finalize the body from the cache and CDN
     *
     * @access private
     */
    private function finalizeBody()
    {
        if ($this->cache->enabled())
        {
            if ($this->cache->has())
            {
                $body = $this->cache()->get();
            }
            else
            {
                $body = $this->CDN->filter($this->body->get());

                $this->cache->put($body);
            }
        }
        else
        {
            $body = $this->CDN->filter($this->body->get());
        }

        $this->body->set($body);
    }

    /**
     * Send the HTTP response
     *
     * @access public
     */
    public function send()
    {
        if (!$this->sent)
        {
            $this->finalize();

            $this->session->save();

            $this->headers->send();

            $this->cookie->send();

            echo $this->body->get();
        }
        
        $this->sent = true;
    }

    /**
     * Immediately send a redirect response
     *
     * @access public
     * @param  string $url    The absolute URL to redirect to
     * @param  int    $status The redirect status (optional) (default 302)
     * @throws \kanso\framework\http\response\exceptions\Stop
     */
    public function redirect(string $url, int $status = 302)
    {
        $this->cache->disable();

        $this->status->set(302);

        $this->headers->set('Location', $url);

        $this->body->clear();

        $this->send();

        throw new Stop();
    }

    /**
     * Send a not found response
     *
     * @access public
     * @throws \kanso\framework\http\response\exceptions\NotFoundException
     */
    public function notFound()
    {
        throw new NotFoundException();
    }

    /**
     * Send a forbidden response
     *
     * @access public
     * @throws \kanso\framework\http\response\exceptions\ForbiddenException
     */
    public function forbidden()
    {
        throw new ForbiddenException();
    }

    /**
     * Send a invalid token response
     *
     * @access public
     * @throws \kanso\framework\http\response\exceptions\InvalidTokenException
     */
    public function invalidToken()
    {
        throw new InvalidTokenException();
    }

    /**
     * Send a invalid token response
     *
     * @access public
     * @throws \kanso\framework\http\response\exceptions\MethodNotAllowedException
     */
    public function methodNotAllowed()
    {
        throw new MethodNotAllowedException();
    }
}
