<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\onion; 

use RuntimeException;
use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\onion\Middleware;

/**
 * Array access trait.
 *
 * @author Joe J. Howard
 */
class Onion
{
    /**
     * Onion layers of middleware
     *
     * @var array
     */
    private $layers = [];
    
    /**
     * Are we peeling a layer ?
     *
     * @var bool
     */
    private $locked = false;

    /**
     * Request object
     *
     * @var \kanso\framework\http\request\Request
     */
    private $request;

    /**
     * Response object
     *
     * @var \kanso\framework\http\response\Response
     */
    private $response;

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\http\request\Request   $request Request object
     * @param  \kanso\framework\http\response\Response $response Response object
     */
    public function __construct(Request $request, Response $response)
    {        
        $this->request = $request;

        $this->response = $response;
    }

    /**
     * Add a layer to the onion
     *
     * @access public
     * @param  mixed $callback   Callback when layer is peeled
     * @param  mixed $parameters Arguments to apply to callback
     * @param  bool  $inner      Add layer to the inner most layer (optional) (default false)\
     * @throws RuntimeException  If the onion is currently being peeled
     */
    public function addLayer($callback, $parameters = null, bool $inner = false)
    {
        if ($this->locked)
        {
            throw new RuntimeException('Middleware can’t be added once the onion is being peeled');
        }

        $layer = new Middleware($callback, $parameters);

        return $inner ? array_unshift($this->layers, $layer) : array_push($this->layers, $layer);
    }

    /**
     * Peel the onion
     *
     * @access public
     */
    public function peel()
    {
        $this->peelLayer();
    }

    /**
     * Peel The next layer
     *
     * @access private
     */
    private function peelLayer()
    {
        if (!empty($this->layers))
        {
            $layer = array_shift($this->layers);

            $this->locked = true;

            $next = $this->getNextLayer();

            $layer->execute($this->request, $this->response, $next);

            $this->locked = false;
        }        
    }

    /**
     * Return a closure for executing the next middleware layer
     *
     * @access private
     * @return \Closure
     */
    private function getNextLayer(): Closure
    {
        if (!empty($this->layers))
        {
            return function()
            {
                $this->peelLayer();
            };
        }

        return function()
        {
            $this->peeled();
        };
    }

    /**
     * Returns the response when the onion is peeled
     *
     * @access private
     * @return \Closure
     */
    public function peeled(): Response
    {
        return $this->response;
    }
}
