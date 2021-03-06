<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services\cli;

use kanso\framework\application\services\Service;
use kanso\framework\exception\ErrorHandler;
use kanso\framework\exception\ErrorLogger;
use kanso\framework\exception\handlers\CliHandler;
use Throwable;

/**
 * Web error handling service.
 *
 * @author Joe J. Howard
 */
class ErrorHandlerService extends Service
{
	/**
	 * Return the error logger if we are logging errors.
	 *
	 * @param  \Throwable                                  $exception "caught" exception
	 * @return \kanso\framework\exception\ErrorLogger|null
	 */
	private function getLogger(Throwable $exception)
	{
		if ($this->container->Config->get('application.error_handler.error_reporting') > 0)
		{
			return new ErrorLogger($exception, $this->container->Filesystem, $this->container->Request->environment(), $this->container->Config->get('application.error_handler.log_path'));
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		// Display errors
		$display_errors = $this->container->Config->get('application.error_handler.display_errors');

		// Log errors
		$error_reporting = $this->container->Config->get('application.error_handler.error_reporting');

		// Create the error handler
		$handler = new ErrorHandler($display_errors, $error_reporting);

		// Cli handler
		$handler->handle(Throwable::class, function($exception) use ($handler, $display_errors)
		{
			// Logger
			$handler->setLogger($this->getLogger($exception));

			// Cli handler
			$cliHandler = new CliHandler($exception, $this->container->Input, $this->container->Output);

			// Handle
			return $cliHandler->handle($display_errors);
		});

		// Save the instance
		$this->container->setInstance('ErrorHandler', $handler);
	}
}
