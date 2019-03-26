<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\exception;

use Closure;
use ErrorException;
use Throwable;

/**
 * Error handler.
 *
 * @author Joe J. Howard
 */
class ErrorHandler
{
	/**
	 * Is the shutdown handler disabled?
	 *
	 * @var bool
	 */
	protected $disableShutdownHandler = false;

	/**
	 * Exception types that shouldn't be logged.
	 *
	 * @var array
	 */
	protected $disableLoggingFor = ['kanso\framework\http\response\exceptions\Stop'];

	/**
	 * Exception handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * User defined "error_reporting()" value before Kanso runs.
	 *
	 * @var int
	 */
	private $defaultErrorReporting;

	/**
	 * User defined "display_errors" value before Kanso runs.
	 *
	 * @var bool
	 */
	private $defaultDisplayErrors;

	/**
	 * Logger.
	 *
	 * @var \kanso\framework\exception\ErrorLogger
	 */
	private $logger;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct(bool $displayErrors, int $errorReporting)
    {
    	// Save the previously set error reporting levels
        $this->defaultErrorReporting = $this->error_reporting();

        $this->defaultDisplayErrors = $this->display_errors();

        // Set the user defined error reporting levels
        $this->display_errors($displayErrors);

        $this->error_reporting($errorReporting);

    	// Add a basic exception handler to the stack as a fullback
		$this->handle(Throwable::class, function($e)
		{
			echo '[ ' . get_class($e) . '] ' . $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]';

			echo PHP_EOL;

			echo $e->getTraceAsString();

			return false;
		});

		$this->register();
    }

	/**
	 * Registers the exception handler.
	 *
	 * @access protected
	 */
	protected function register()
	{
		// Allows us to handle "fatal" errors
		register_shutdown_function(function()
		{
			$e = error_get_last();

			if($e !== null && ($this->error_reporting() & $e['type']) !== 0 && !$this->disableShutdownHandler)
			{
				$this->handler(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

				exit(1);
			}
		});

		// Set the exception handler
		set_exception_handler([$this, 'handler']);
	}

	/**
	 * Set logger instance.
	 *
	 * @access public
	 * @param \kanso\framework\exception\ErrorLogger $logger Error logger
	 */
	public function setLogger(ErrorLogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Disables logging for an exception type.
	 *
	 * @access public
	 * @param string|array $exceptionType Exception type or array of exception types
	 */
	public function disableLoggingFor($exceptionType)
	{
		$this->disableLoggingFor = array_unique(array_merge($this->disableLoggingFor, (array) $exceptionType));
	}

	/**
	 * Disables the shutdown handler.
	 *
	 * @access public
	 */
	public function disableShutdownHandler()
	{
		$this->disableShutdownHandler = true;
	}

	/**
	 * Prepends an exception handler to the stack.
	 *
	 * @access public
	 * @param string   $exceptionType Exception type
	 * @param \Closure $handler       Exception handler
	 */
	public function handle(string $exceptionType, Closure $handler)
	{
		array_unshift($this->handlers, ['exceptionType' => $exceptionType, 'handler' => $handler]);
	}

	/**
	 * Clears all error handlers for an exception type.
	 *
	 * @access public
	 * @param string $exceptionType Exception type
	 */
	public function clearHandlers(string $exceptionType)
	{
		foreach($this->handlers as $key => $handler)
		{
			if($handler['exceptionType'] === $exceptionType)
			{
				unset($this->handlers[$key]);
			}
		}
	}

	/**
	 * Replaces all error handlers for an exception type with a new one.
	 *
	 * @access public
	 * @param string   $exceptionType Exception type
	 * @param \Closure $handler       Exception handler
	 */
	public function replaceHandlers(string $exceptionType, Closure $handler)
	{
		$this->clearHandlers($exceptionType);

		$this->handle($exceptionType, $handler);
	}

    /**
     * Restore the default error handler.
     *
     * @access public
     */
    public function restore()
    {
        $this->display_errors($this->defaultDisplayErrors);

        $this->error_reporting($this->defaultErrorReporting);

    	restore_error_handler();

    	restore_exception_handler();
    }

	/**
	 * Clear output buffers.
	 *
	 * @access protected
	 */
	protected function clearOutputBuffers()
	{
		while(ob_get_level() > 0) ob_end_clean();
	}

	/**
	 * Should the exception be logged?
	 *
	 * @access protected
	 * @param  \Throwable $exception An exception object
	 * @return bool
	 */
	protected function shouldExceptionBeLogged(Throwable $exception): bool
	{
		$error_reporting = $this->error_reporting();

		$code = intval($exception->getCode());

		// No error reporting or no error logging
		if (!$error_reporting || !$this->logger)
		{
			return false;
		}

		if ($error_reporting > 0 && $error_reporting >= $code)
		{
			foreach($this->disableLoggingFor as $exceptionType)
			{
				if ($exception instanceof $exceptionType)
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Handles uncaught exceptions.
	 *
	 * @access public
	 * @param \Throwable $exception An exception object
	 */
	public function handler(Throwable $exception)
	{
		try
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// Loop through the exception handlers

			foreach($this->handlers as $handler)
			{
				if($exception instanceof $handler['exceptionType'])
				{
					if($handler['handler']($exception) !== null)
					{
						break;
					}
				}
			}

			// Log exception
			if($this->shouldExceptionBeLogged($exception))
			{
				$this->logger->write();
			}
		}
		catch(Throwable $e)
		{
			// Empty output buffers

			$this->clearOutputBuffers();

			// One of the exception handlers failed so we'll just show the user a generic error screen

			echo $e->getMessage() . ' on line [ ' . $e->getLine() . ' ] in [ ' . $e->getFile() . ' ]' . PHP_EOL;
		}

		exit(1);
	}

    /**
     * Set or get the Kanso error reporting level.
     *
     * @access public
     * @param  int|null $errorReporting (optional) (default NULL)
     * @return int
     */
    public function error_reporting(int $errorReporting = null): int
    {
    	if (!is_null($errorReporting))
    	{
    		error_reporting($errorReporting);

    		ini_set('error_reporting', strval($errorReporting));
    	}

    	return error_reporting();
    }

    /**
     * Set or get the Kanso "display_errors" value.
     *
     * @access public
     * @param  bool|null $display_errors (optional) (default NULL)
     * @return bool
     */
    public function display_errors(bool $display_errors = null): bool
    {
    	if (!is_null($display_errors))
    	{
    		ini_set('display_errors', $display_errors === true ? '1' : '0');
    	}

    	return ini_get('display_errors');
    }
}
