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
    	$error_msg = '';
		$className = str_replace('.php', '', substr($this->file, strrpos($this->file, '/Kanso') + 1));
		$trace     = trim(implode(",\n\t\t ", $this->generateCallTrace()));
		$name      = $this->erroName($this->code);

		# Failsafe we need to try to get the Kanso Environment
		# However an error could occur before it has been instantiated
		# (Like during installation)
		# So we are re-instantiating the Env array here
		$env = \Kanso\Environment::extract();
		$URL = $env['REQUEST_URL'];
		$IP  = $env['CLIENT_IP_ADDRESS'];

		$error_msg .= "DATE    : ".date('l jS \of F Y h:i:s A')."\n";
    	$error_msg .= "TYPE    : $name [$this->code]\n";
        $error_msg .= "URL     : $URL\n";
        $error_msg .= "CLASS   : $className\n";
        $error_msg .= "FILE    : $this->file\n";
        $error_msg .= "LINE    : $this->line\n";
        $error_msg .= "MESSAGE : $this->message\n";
        $error_msg .= "IP      : $IP\n";
        $error_msg .= "TRACE   : $trace\n\n\n";

		$this->toFile($error_msg);

		return $error_msg;
    }

    /**
	 * Convert error type into string
	 *
	 * @param  type        CONSTANT|INT
	 * @return string
	 * @see     http://php.net/manual/en/errorfunc.constants.php
	 */
    function erroName($type) 
	{ 
	    switch($type) 
	    { 
	        case E_ERROR: // 1 // 
	            return 'E_ERROR'; 
	        case E_WARNING: // 2 // 
	            return 'E_WARNING'; 
	        case E_PARSE: // 4 // 
	            return 'E_PARSE'; 
	        case E_NOTICE: // 8 // 
	            return 'E_NOTICE'; 
	        case E_CORE_ERROR: // 16 // 
	            return 'E_CORE_ERROR'; 
	        case E_CORE_WARNING: // 32 // 
	            return 'E_CORE_WARNING'; 
	        case E_COMPILE_ERROR: // 64 // 
	            return 'E_COMPILE_ERROR'; 
	        case E_COMPILE_WARNING: // 128 // 
	            return 'E_COMPILE_WARNING'; 
	        case E_USER_ERROR: // 256 // 
	            return 'E_USER_ERROR'; 
	        case E_USER_WARNING: // 512 // 
	            return 'E_USER_WARNING'; 
	        case E_USER_NOTICE: // 1024 // 
	            return 'E_USER_NOTICE'; 
	        case E_STRICT: // 2048 // 
	            return 'E_STRICT'; 
	        case E_RECOVERABLE_ERROR: // 4096 // 
	            return 'E_RECOVERABLE_ERROR'; 
	        case E_DEPRECATED: // 8192 // 
	            return 'E_DEPRECATED'; 
	        case E_USER_DEPRECATED: // 16384 // 
	            return 'E_USER_DEPRECATED'; 
	    } 
	    return "UNKOWN_ERROR"; 
	} 

	/**
	 * Write the error to file
	 *
	 */
    private function toFile($msg)
    {
    	file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'error_log.txt', $msg, FILE_APPEND);
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