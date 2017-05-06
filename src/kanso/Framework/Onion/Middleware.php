<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Onion; 

use Closure;
use Kanso\Framework\Http\Request\Request;
use Kanso\Framework\Http\Response\Response;
use Kanso\Framework\Utility\Callback;

/**
 * Middleware object
 *
 * @author Joe J. Howard
 */
class Middleware
{   
    /**
     * Callback
     *
     * @var mixed
     */
    private $callback;

    /**
     * Callback args
     *
     * @var mixed
     */
    private $args;

    /**
     * Constructor
     *
     * @access public
     * @param  mixed  $callback    Callback to use
     * @param  mixed  $args        Arguments to apply to callback (optional) (default null)
     */
    public function __construct($callback, $args = null)
	{
		$this->callback = $callback;

		$this->args = Callback::normalizeArgs($args);
	}

    /**
     * Execute the callback
     *
     * @access public
     * @param  \Kanso\Framework\Http\Request\Request   $request  
     * @param  \Kanso\Framework\Http\Response\Response $response
     * @param  \Closure                                $next
     */
    public function execute(Request $request, Response $response, Closure $next)
    {        
        $args = array_merge([$request, $response, $next], $this->args);
        
        return Callback::apply($this->callback, $args);
    }
}
