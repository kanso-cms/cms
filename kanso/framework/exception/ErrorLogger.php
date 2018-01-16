<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\exception;

use kanso\framework\http\request\Environment;
use kanso\framework\exception\ExceptionLogicTrait;
use Throwable;

/**
 * Error logger class 
 *
 * @author Joe J. Howard
 */
class ErrorLogger
{
    use ExceptionLogicTrait;

    /**
     * Directory where logs are stored
     *
     * @var string
     */
    private $path;

    /**
     * HttpEnv instance
     *
     * @var \kanso\framework\http\request\Environment;
     */
    private $httpEnv;

	/**
	 * Constructor
	 *
	 * @access public
     * @param \Throwable                                $exception Throwable
     * @param \kanso\framework\http\request\Environment $httpEnv   HttpEnv instance for logging details
     * @param string                                    $path Directory to store log files in
	 */
    public function __construct(Throwable $exception, Environment $httpEnv, string $path) 
    {
        $this->path = $path;

        $this->httpEnv = $httpEnv;

        $this->setException($exception);
    }

    /**
     * Write the current exception to file
     *
     * @access public
     */
    public function write()
    {
        $msg = $this->logMsg();

        file_put_contents($this->genericPath(), $msg, FILE_APPEND);
        
        file_put_contents($this->errnoPath(), $msg, FILE_APPEND);
    }

    /**
     * Set the error logs directory
     *
     * @access public
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Build and return the log text
     *
     * @access private
     * @return string
     */
    private function logMsg(): string
    {
        return 
        'DATE    : '.date('l jS \of F Y h:i:s A', time())."\n".
        'TYPE    : '.$this->errType().' ['.$this->exception->getCode()."]\n".
        'URL     : '.$this->httpEnv->REQUEST_URL."\n".
        'CLASS   : '.$this->errClass()."\n".
        'FILE    : '.$this->exception->getFile()."\n".
        'LINE    : '.$this->exception->getLine()."\n".
        'MESSAGE : '.$this->exception->getMessage()."\n".
        'IP      : '.$this->httpEnv->REMOTE_ADDR."\n".
        'TRACE   : '.ltrim(implode("\n\t\t ", $this->errTrace()))."\n\n\n";
    }

    /**
     * Get the path to generic error log file
     *
     * @access private
     * @return string
     */
    private function genericPath(): string
    {
        return $this->path.DIRECTORY_SEPARATOR.date('d_m_y').'_all_errors.log';
    }

    /**
     * Get the path to the specific error log file for current error
     *
     * @access private
     * @return string
     */
    private function errnoPath(): string
    {
        return $this->path.DIRECTORY_SEPARATOR.date('d_m_y').'_'.$this->errnoToFile().'.log';
    }

    /**
     * Convert the error code to the log file name
     *
     * @access private
     * @return string
     */
    private function errnoToFile(): string
    {
        if($this->exception instanceof PDOException || get_class($this->exception) === 'PDOException' || strpos($this->exception->getMessage(), 'SQLSTATE') !== false)
        {
            return 'database_errors'; 
        }

        switch($this->exception->getCode()) 
        {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'fatal_errors'; 

            case E_WARNING:
            case E_NOTICE:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'nonfatal_errors'; 

            default:
                return "other_errors"; 
        }
    }
}