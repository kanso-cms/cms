<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\controller;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Callback;

/**
 * Base controller.
 *
 * @author Joe J. Howard
 */
abstract class Controller
{
	use ControllerHelperTrait;

	/**
	 * Next middleware closure.
	 *
	 * @var \Closure
	 */
	protected $nextMiddleware;

	/**
	 * Model.
	 *
	 * @var mixed
	 */
	protected $model;

    /**
     * Constructor.
     *
     * @param \kanso\framework\http\request\Request   $request    Request instance
     * @param \kanso\framework\http\response\Response $response   Response instance
     * @param \Closure                                $next       Next middleware closure
     * @param string                                  $modelClass Full namespaced class name of the model
     */
    public function __construct(Request $request, Response $response, Closure $next, string $modelClass)
    {
    	$this->nextMiddleware = $next;

    	$this->loadModel($modelClass);
    }

    /**
     * Loads and instantiates the model.
     *
     * @param string $className Full namespaced class name of the model
     */
    private function loadModel(string $className): void
	{
		$this->model = $this->instantiateModel($className);
	}

	/**
	 * Instantiates and returns the model instance.
	 *
	 * @param  string $class Full namespaced class name of the model
	 * @return object
	 */
	private function instantiateModel(string $class)
	{
		return Callback::newClass($class);
	}
}
