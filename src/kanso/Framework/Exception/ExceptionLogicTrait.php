<?php

namespace kanso\framework\exception;

use Throwable;
use ErrorException;
use \kanso\framework\utility\Str;

/**
 * Exception helper functions
 *
 * @author Joe J. Howard
 */
trait ExceptionLogicTrait
{
	/**
	 * "Context" for error line
	 *
	 * @var int
	 */
	protected $sourcePadding = 6;

	/**
	 * Exception object
	 *
	 * @var \Throwable
	 */
	protected $exception;

	/**
	 * Set the exception object
	 *
	 * @access protected
	 * @param \Throwable $exception Throwable
	 */
	protected function setException(Throwable $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * Get text version of PHP error constant
	 *
	 * @access protected
	 * @see    http://php.net/manual/en/errorfunc.constants.php
	 * @return string
	 */
	protected function errType(): string
	{
		if($this->exception instanceof ErrorException || get_class($this->exception) === 'ErrorException')
		{
			$code = $this->exception->getCode();

			$codes =
			[
				E_ERROR             => 'E_ERROR',
				E_PARSE             => 'E_PARSE',
				E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
				E_STRICT            => 'E_STRICT',
				E_NOTICE            => 'E_NOTICE',
				E_WARNING           => 'E_WARNING',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED        => 'E_DEPRECATED',
				E_USER_NOTICE       => 'E_USER_NOTICE',
				E_USER_WARNING      => 'E_USER_WARNING',
				E_USER_ERROR        => 'E_USER_ERROR',
				E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'E_ERROR';
		}
		else if ($this->exception instanceof RequestException || $this->exceptionParentName() === 'RequestException')
		{
			return Str::camel2case($this->exceptionClassName());
		}

		return Str::camel2case($this->exceptionClassName());
	}

	/**
	 * Get the current exception class without namespace
	 *
	 * @access protected
	 * @return string
	 */
	protected function exceptionClassName(): string
	{
		$class = explode('\\', get_class($this->exception));

		return array_pop($class);
	}

	/**
	 * Get the current exception's parent class without namespace
	 *
	 * @access protected
	 * @return string
	 */
	protected function exceptionParentName(): string
	{
		$class = explode('\\', get_parent_class($this->exception));

		return array_pop($class);
	}

	/**
	 * Convert PHP error code to pretty name
	 *
	 * @access protected
	 * @see    http://php.net/manual/en/errorfunc.constants.php
	 * @return string
	 */
	protected function errName(): string
	{
		if($this->exception instanceof ErrorException || get_class($this->exception) === 'ErrorException')
		{
			$code = $this->exception->getCode();

			$codes =
			[
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_STRICT            => 'Strict Mode Error',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'Error Exception';
		}
		else if ($this->exception instanceof RequestException || $this->exceptionParentName() === 'RequestException')
		{
			return Str::camel2case($this->exceptionClassName());
		}

		return Str::camel2case($this->exceptionClassName());
	}

	/**
	 * Get the exception call trace
	 *
	 * @access protected
	 * @return array
	 */
    protected function errTrace(): array
	{
	    $trace = array_reverse(explode("\n", $this->exception->getTraceAsString()));
	    
	    array_shift($trace);

	    array_pop($trace);
	    
	    $length = count($trace);
	    
	    $result = [];

	    foreach ($trace as $call)
	    {
	    	$result[] = substr($call, strpos($call, ' '));
	    }
	    
	    return $result;
	}

	/**
	 * Get source code of error line context
	 *
	 * @access protected
	 * @return array
	 */
	protected function errSource(): array
	{
		if(!is_readable($this->exception->getFile()))
		{
			return [];
		}

		$handle      = fopen($this->exception->getFile(), 'r');
		$lines       = [];
		$currentLine = 0;

		while(!feof($handle))
		{
			$currentLine++;

			$sourceCode = fgets($handle);

			if($currentLine > $this->exception->getLine() + $this->sourcePadding)
			{
				break; // Exit loop after we have found what we were looking for
			}

			if($currentLine >= ($this->exception->getLine() - $this->sourcePadding) && $currentLine <= ($this->exception->getLine() + $this->sourcePadding))
			{
				$lines[$currentLine] = $sourceCode;
			}
		}

		fclose($handle);

		return $lines;
	}

	/**
	 * Get the classname of the error file
	 *
	 * @access protected
	 * @return sting
	 */
	protected function errClass(): string
	{		
		if(!is_readable($this->exception->getFile()))
		{
			return '';
		}

		$handle      = fopen($this->exception->getFile(), 'r');
		$class      = '';
		$namespace  = '';
		$tokens     = token_get_all(file_get_contents($this->exception->getFile()));

		foreach ($tokens as $i => $token)
	    {
	        if ($token[0] === T_NAMESPACE)
	        {
	            foreach ($tokens as $j => $_token) 
	            {
	                if ($_token[0] === T_STRING)
	                {
	                    $namespace .= '\\'.$_token[1];
	                } 
	                else if ($_token === '{' || $_token === ';')
	                {
	                    break;
	                }
	            }
	        }
	        else if ($token[0] === T_CLASS)
	        {
	            foreach ($tokens as $j => $_token)
	            {
	                if ($_token === '{')
	                {
	                    $class = $tokens[$i+2][1];
	                }
	            }
	        }
	    }

	    if (empty($class))
	    {
	    	return '';
	    }

		return $namespace.'\\'.$class;
	}
}
