<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\onion;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Callback;

/**
 * Middleware object.
 *
 * @author Joe J. Howard
 */
class Middleware
{
    /**
     * Callback.
     *
     * @var mixed
     */
    private $callback;

    /**
     * Callback args.
     *
     * @var mixed
     */
    private $args;

    /**
     * Constructor.
     *
     * @param mixed $callback Callback to use
     * @param mixed $args     Arguments to apply to callback (optional) (default null)
     */
    public function __construct($callback, $args = null)
	{
		$this->callback = $callback;

		$this->args = Callback::normalizeArgs($args);
	}

    /**
     * Execute the callback.
     *
     * @param \kanso\framework\http\request\Request   $request
     * @param \kanso\framework\http\response\Response $response
     * @param \Closure                                $next
     */
    public function execute(Request $request, Response $response, Closure $next)
    {
        $args = array_merge([$request, $response, $next], $this->args);

        return Callback::apply($this->callback, $args);
    }

    /**
     * Returns the callback.
     *
     * @return closure|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Returns the callback.
     *
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }
}
