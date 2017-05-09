<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\controller;

use Closure;
use kanso\Kanso;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Callback;

/**
 * Base controller
 *
 * @author Joe J. Howard
 */
abstract class Controller
{
	use ControllerHelperTrait;

	/**
	 * Next middleware closure
	 *
	 * @var \Closure
	 */
	protected $nextMiddleware;

	/**
	 * Model
	 *
	 * @var mixed
	 */
	protected $model;

	/**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\http\request\Request  $request    Request instance
     * @param  \kanso\framework\http\request\Response $response   Response instance
     * @param  \Closure                               $next       Next middleware closure
     * @param  string                                 $modelClass Full namespaced class name of the model

     */
    public function __construct(Request $request, Response $response, Closure $next, string $modelClass)
    {
    	$this->request = $request;

    	$this->response = $response;

    	$this->nextMiddleware = $next;

    	$this->loadContainer();

    	$this->loadModel($modelClass);
    }

   	/**
	 * Loads the container into the container aware trait
	 *
	 * @access private
	 */
    private function loadContainer()
	{
		$this->setContainer(Kanso::instance()->container());
	}

	/**
	 * Loads and instantiates the model
	 *
	 * @access private
	 * @param  string  $class Full namespaced class name of the model
	 */
    private function loadModel(string $className)
	{
		$this->model = $this->instantiateModel($className);
	}

    /**
	 * Instantiates and returns the model instance
	 *
	 * @access private
	 * @param  string  $class Full namespaced class name of the model
	 * @return object
	 */
	private function instantiateModel(string $class)
	{
		return Callback::newClass($class);
	}
}