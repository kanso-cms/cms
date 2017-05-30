<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\exception\handlers;

use Throwable;
use kanso\framework\exception\ExceptionLogicTrait;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\mvc\view\View;
use kanso\framework\http\exceptions\RequestException;
use kanso\framework\http\exceptions\MethodNotAllowedException;

/**
 * Error web handler
 *
 * @author Joe J. Howard
 */
class WebHandler
{
	use ExceptionLogicTrait;

	/**
	 * Request instance.
	 *
	 * @var \kanso\framework\http\Request
	 */
	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \kanso\framework\http\Response
	 */
	protected $response;

	/**
	 * View instance.
	 *
	 * @var \kanso\framework\http\View
	 */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \Throwable          	 		  $exception Throwable
	 * @param \kanso\framework\http\Request   $request   Request instance
	 * @param \kanso\framework\http\Response  $response  Response instance
	 * @param \kanso\framework\mvc\view\View  $view      View instance
	 */
	public function __construct(Throwable $exception, Request $request, Response $response, View $view)
	{
		$this->request = $request;

		$this->response = $response;

		$this->view = $view;

		$this->exception = $exception;
	}

	/**
	 * Should we return the error as JSON?
	 *
	 * @access protected
	 * @return bool
	 */
	protected function returnAsJson(): bool
	{
		$jsonMimeTypes = ['application/json', 'text/json'];

		if($this->request->isAjax() || in_array($this->response->format()->get(), $jsonMimeTypes))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns a detailed error page.
	 *
	 * @access protected
	 * @param  bool      $returnAsJson Should we return JSON?
	 * @return string
	 */
	protected function getDetailedError(bool $returnAsJson): string
	{
		$vars = [
    		'errcode'      => $this->exception->getCode(),
    		'errName'      => $this->errName(),
    		'errtype'      => $this->errtype(),
    		'errtime'      => time(),
    		'errmsg'       => $this->exception->getMessage(),
    		'errfile'      => $this->exception->getFile(),
    		'errline'      => intval($this->exception->getLine()),
    		'errClass'     => $this->errClass(),
    		'errTrace'     => $this->errTrace(),
    		'errUrl'       => $this->request->environment()->REQUEST_URL,
    		'clientIP'     => $this->request->environment()->REMOTE_ADDR,
    		'logFiles'     => [],
    		'errFileLines' => $this->errSource(),
    	];

		if($returnAsJson)
		{
			return json_encode($vars);
		}
		else
		{
			// Return detailed error view
			return $this->view->display(dirname(__FILE__).'/views/debug.php', $vars);
		}
	}

	/**
	 * Returns a generic error page.
	 *
	 * @access protected
	 * @param  bool      $returnAsJson Should we return JSON?
	 * @return string
	 */
	protected function getGenericError(bool $returnAsJson): string
	{
		$code = $this->exception->getCode();

		if($returnAsJson)
		{
			switch($code)
			{
				case 403:
					$message = 'You don\'t have permission to access the requested resource.';
					break;
				case 404:
					$message = 'The resource you requested could not be found. It may have been moved or deleted.';
					break;
				case 405:
					$message = 'The request method that was used is not supported by this resource.';
					break;
				default:
					$message = 'An error has occurred while processing your request.';
			}

			return json_encode(['message' => $message]);
		}
		else
		{
			$dir = dirname(__FILE__).'/views';

			$view = $dir.'/500.php';

			if($this->exception instanceof RequestException || $this->exceptionParentName() === 'RequestException')
			{
				if (file_exists($dir.'/'.$code.'.php'))
				{
					$view = $dir.'/'.$code.'.php';
				}
			}

			return $this->view->display($view);
		}
	}

	/**
	 * Display an error page to end user
	 *
	 * @access protected
	 * @param  bool      $showDetails Should we show a detailed error page 
	 * @return false
	 */
	public function handle(bool $showDetails = true): bool
	{

		# Set appropriate content type header

		if(($returnAsJson = $this->returnAsJson()) === true)
		{
			$this->response->format()->set('application/json');
		}
		else
		{
			$this->response->format()->set('text/html');
		}

		# Set the response body

		if($showDetails)
		{
			$this->response->body()->set($this->getDetailedError($returnAsJson));
		}
		else
		{
			$this->response->body()->set($this->getGenericError($returnAsJson));
		}

		# Send the response along with appropriate headers

		if($this->exception instanceof RequestException || $this->exceptionParentName() === 'RequestException')
		{
			$status = $this->exception->getCode();

			if($this->exception instanceof MethodNotAllowedException || $this->exceptionClassName() === 'MethodNotAllowedException')
			{
				$this->response->headers()->set('allows', implode(',', $this->exception->getAllowedMethods()));
			}
		}
		else
		{
			$status = 500;
		}
		
		$this->response->status()->set($status);

		$this->response->cache()->disable();

		$this->response->send();

		// Return false to stop further error handling

		return false;
	}
}
