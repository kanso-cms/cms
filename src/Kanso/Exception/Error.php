<?php
namespace Kanso\Exception;

/**
 * Error Exception
 *
 * This exception gives infomation on the classname
 * as well as the CallTrace
 */
class Error extends \Exception
{

	/**
	 * Constructor
	 *
	 * @param string       $message
	 * @param in           $code
	 * @param Exception    $previous
	 *
	 */
    public function __construct($errstr, $errno = 0, $errfile, $errline, \Exception $previous = null) 
    {
        parent::__construct($errstr, $errno, $previous);
        $this->file = $errfile;
        $this->line = $errline;
    }

    /**
	 * Convert error messages to readable string
	 *
	 * @return string
	 */
    public function __toString() 
    {
    	$trace     = $this->generateCallTrace();
    	$className = str_replace('.php', '', substr($this->file, strrpos($this->file, '/Kanso') + 1));
    	$errorMsg  = "$className Exception: $this->message\n";
    	$errorMsg .= "Trace: \n".implode(",\n", $trace);
        return $errorMsg;
    }

    /**
	 * Generate an errors call trace
	 *
	 * @return array
	 */
    private function generateCallTrace()
	{
	    $e 	   = $this;
	    $trace = explode("\n", $e->getTraceAsString());
	    $trace = array_reverse($trace);
	    array_shift($trace);
	    array_pop($trace);
	    $length = count($trace);
	    $result = [];
	    
	    for ($i = 0; $i < $length; $i++)
	    {
	        $result[] = substr($trace[$i], strpos($trace[$i], ' '));
	    }
	    
	    return $result;
	}

}