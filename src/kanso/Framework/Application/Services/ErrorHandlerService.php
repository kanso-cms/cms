<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Throwable;
use Kanso\Framework\Application\Services\Service;
use Kanso\Framework\Exception\ErrorHandler;
use Kanso\Framework\Exception\ErrorLogger;
use Kanso\Framework\Exception\Handlers\WebHandler;

/**
 * Error handling service
 *
 * @author Joe J. Howard
 */
class ErrorHandlerService extends Service
{
	/**
	 * Return the error logger if we are logging errors
	 *
	 * @access private
	 * @param  \Throwable $exception "caught" exception
	 * @return \Kanso\Framework\Exception\ErrorLogger|null
	 */	
	private function getLogger(Throwable $exception)
	{
		if ($this->container->Config->get('application.error_handler.error_reporting') > 0)
		{
			return new ErrorLogger($exception, $this->container->Request->environment(), $this->container->Config->get('application.error_handler.log_path'));
		}

		return null;
	}
	 
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		# Display errors
		$display_errors = $this->container->Config->get('application.error_handler.display_errors');

		# Log errors
		$error_reporting = $this->container->Config->get('application.error_handler.error_reporting');

		# Create the error handler
		$handler = new ErrorHandler($display_errors, $error_reporting);

		# Web handler
		$handler->handle(Throwable::class, function($exception) use ($handler, $display_errors)
		{
			# Logger
			$handler->setLogger($this->getLogger($exception));

			# Web handler
			$webHandler = new WebHandler($exception, $this->container->Request, $this->container->Response, $this->container->View);

			# Handle
			return $webHandler->handle($display_errors);
		}); 

		# Save the instance
		$this->container->instance('ErrorHandler', $handler);
	}
}
