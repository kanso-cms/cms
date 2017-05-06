<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Response;

use Kanso\Framework\Http\Response\Format;
use Kanso\Framework\Http\Response\Body;
use Kanso\Framework\Http\Response\Status;
use Kanso\Framework\Http\Response\Headers;
use Kanso\Framework\Http\Response\Cache;
use Kanso\Framework\Http\Cookie\Cookie;
use Kanso\Framework\Http\Session\Session;
use Kanso\Framework\Http\Response\Protocol;
use Kanso\Framework\View\View;
use Kanso\Framework\Http\Response\Exceptions\NotFoundException;
use Kanso\Framework\Http\Response\Exceptions\Stop;

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
     * @var \Kanso\Framework\Http\Response\Protocol
     */
    private $protocol;

    /**
     * The HTTP format
     *
     * @var \Kanso\Framework\Http\Response\Format
     */
    private $format;

    /**
     * The HTTP headers
     *
     * @var \Kanso\Framework\Http\Response\Headers
     */
    private $headers;

    /**
     * Cookie manager
     *
     * @var \Kanso\Framework\Http\Cookie\Cookie
     */
    private $cookie;

    /**
     * Response body
     *
     * @var \Kanso\Framework\Http\Response\Body
     */
    private $body;

    /**
     * Response status
     *
     * @var \Kanso\Framework\Http\Response\Status
     */
    private $status;

    /**
     * Response cache
     *
     * @var \Kanso\Framework\Http\Response\Cache
     */
    private $cache;

    /**
     * CDN manager
     *
     * @var \Kanso\Framework\Http\Response\CDN
     */
     private $CDN;

    /**
     * View renderer
     *
     * @var \Kanso\Framework\View\View
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
     * @param  \Kanso\Framework\Http\Response\Protocol $protocol 
     * @param  \Kanso\Framework\Http\Response\Format   $format
     * @param  \Kanso\Framework\Http\Response\Body     $body
     * @param  \Kanso\Framework\Http\Response\Status   $status     
     * @param  \Kanso\Framework\Http\Response\Headers  $headers
     * @param  \Kanso\Framework\Http\Session\Session   $session
     * @param  \Kanso\Framework\Http\Response\Cache    $cache
     * @param  \Kanso\Framework\Http\Response\CDN      $CDN
     * @param  \Kanso\Framework\View\View              $view
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
     * @return \Kanso\Framework\Http\Response\Protocol
     */
    public function protocol()
    {
        return $this->protocol();
    }

    /**
     * Get the body object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Body
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Get the format object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Format
     */
    public function format()
    {
        return $this->format;
    }

    /**
     * Get the status object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Status
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the headers object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Headers
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the cache object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Cache
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * Get the cookie manager
     *
     * @access public
     * @return \Kanso\Framework\Http\Cookie\Cookie
     */
    public function cookie()
    {
        return $this->cookie;
    }

    /**
     * Get the session manager
     *
     * @access public
     * @return \Kanso\Framework\Http\Session\Session
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Get the CDN object
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Cache
     */
    public function CDN()
    {
        return $this->CDN;
    }

    /**
     * Get the view object
     *
     * @access public
     * @return \Kanso\Framework\View\View
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
    public function finalize()
    {
        $this->headers->set($this->protocol->get(), $this->status->get().' '.$this->status->message());

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
     * @return \Kanso\Framework\Http\Response\Response
     */
    public function redirect(string $url, int $status = 302)
    {
        $this->cache->disable();

        $this->status->set(302);

        $this->headers->set('Location', $url);

        $this->body->clear();

        $this->send();

        throw new stop();
    }

    /**
     * Send a not found response
     *
     * @access public
     * @return \Kanso\Framework\Http\Response\Response
     */
    public function notFound()
    {
        throw new NotFoundException();
    }
}
