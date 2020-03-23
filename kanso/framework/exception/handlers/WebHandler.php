<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\exception\handlers;

use kanso\framework\exception\ExceptionLogicTrait;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\exceptions\MethodNotAllowedException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\http\response\Response;
use kanso\framework\mvc\view\View;
use Throwable;

/**
 * Error web handler.
 *
 * @author Joe J. Howard
 */
class WebHandler
{
	use ExceptionLogicTrait;

	/**
	 * Request instance.
	 *
	 * @var \kanso\framework\http\request\Request
	 */
	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \kanso\framework\http\response\Response
	 */
	protected $response;

	/**
	 * View instance.
	 *
	 * @var \kanso\framework\mvc\view\View
	 */
	protected $view;

	/**
	 * Error.
	 *
	 * @var \Throwable|\kanso\framework\http\response\exceptions\ForbiddenException|\kanso\framework\http\response\exceptions\InvalidTokenException|\kanso\framework\http\response\exceptions\MethodNotAllowedException|\kanso\framework\http\response\exceptions\NotFoundException|\kanso\framework\http\response\exceptions\RequestException|\kanso\framework\http\response\exceptions\Stop|\Exception
	 */
	protected $exception;

	/**
	 * Constructor.
	 *
	 * @param \Throwable                              $exception Throwable
	 * @param \kanso\framework\http\request\Request   $request   Request instance
	 * @param \kanso\framework\http\response\Response $response  Response instance
	 * @param \kanso\framework\mvc\view\View          $view      View instance
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
	 * @param  bool   $returnAsJson Should we return JSON?
	 * @param  bool   $isBot        Is the user-agent a bot?
	 * @return string
	 */
	protected function getDetailedError(bool $returnAsJson, bool $isBot): string
	{
		$vars =
		[
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

    	// Bots get a plain error message
    	if ($isBot)
    	{
    		return $vars['errmsg'];
    	}

		if ($returnAsJson)
		{
			return json_encode($vars);
		}
		else
		{
			// Return detailed error view
			return $this->view->display(dirname(__FILE__) . '/views/debug.php', $vars);
		}
	}

	/**
	 * Returns a generic error page.
	 *
	 * @param  bool   $returnAsJson Should we return JSON?
	 * @param  bool   $isBot        Is the user-agent a bot?
	 * @return string
	 */
	protected function getGenericError(bool $returnAsJson, bool $isBot): string
	{
		$code        = $this->exception->getCode();
		$message     = $this->response->status()->message($code);
		$description = $this->response->status()->description($code);

		if ($isBot)
		{
			return $description;
		}
		elseif ($returnAsJson)
		{
			return json_encode(['message' => $message]);
		}
		else
		{
			$data =
			[
				'code'        => $code,
				'message'     => $message,
				'description' => $description,
			];

			$view = dirname(__FILE__) . '/views/generic.php';

			return $this->view->display($view, $data);
		}
	}

	/**
	 * Display an error page to end user.
	 *
	 * @param  bool  $debug Is debugging enabled
	 * @return false
	 */
	public function handle(bool $debug = true): bool
	{
		// Set appropriate content type header
		if (($returnAsJson = $this->returnAsJson()) === true)
		{
			$this->response->format()->set('application/json');
		}
		else
		{
			$this->response->format()->set('text/html');
		}

		// Set the response body
		if ($debug)
		{
			$this->response->body()->set($this->getDetailedError($returnAsJson, $this->request->isBot()));
		}
		else
		{
			$this->response->body()->set($this->getGenericError($returnAsJson, $this->request->isBot()));
		}

		// Send the response along with appropriate headers
		if ($this->exception instanceof RequestException)
		{
			$status = $this->exception->getCode();

			if ($this->exception instanceof MethodNotAllowedException)
			{
				$this->response->headers()->set('allows', implode(',', $this->exception->getAllowedMethods()));
			}
		}
		else
		{
			$status = 500;
		}

		$this->response->status()->set($status);

		$this->response->send();

		// Return false to stop further error handling
		return false;
	}
}
