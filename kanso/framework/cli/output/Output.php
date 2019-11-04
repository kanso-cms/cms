<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli\output;

use kanso\framework\cli\output\Formatter;
use kanso\framework\cli\Environment;

/**
 * Output
 *
 * @author Joe J. Howard
 */
class Output
{
	/**
	 * Formatter.
	 *
	 * @var \kanso\framework\cli\output\Formatter
	 */
	private $formatter;

	/**
     * Environment instance
     *
     * @var \kanso\framework\cli\Environment
     */
    private $environment;

	/**
	 * Constructor.
	 *
	 * @param  \kanso\framework\cli\output\Formatter $formatter   Formatter   instance
	 * @param  \kanso\framework\cli\Environment      $environment Environment instance
	 */
	public function __construct(Formatter $formatter, Environment $environment)
	{
		$this->formatter = $formatter;

		$this->environment = $environment;
	}

	/**
     * Returns the formatter
     *
     * @access public
     * @return \\kanso\framework\cli\output\Formatter
     */
    public function formatter(): Formatter
    {
        return $this->formatter;
    }
    
    /**
     * Returns the environment
     *
     * @access public
     * @return \kanso\framework\cli\Environment
     */
    public function environment(): environment
    {
        return $this->environment;
    }

	/**
	 * Writes string to output.
	 *
	 * @param string $string String to write
	 * @param int    $writer Output type
	 */
	public function write(string $string): void
	{
		if ($this->environment->hasAnsiSupport() === false)
		{
			$string = $this->formatter->stripTags($string);
		}

		$string = $this->formatter->format($string);

		fwrite(STDOUT, $string);
	}

	/**
	 * Appends newline to string and writes it to output.
	 *
	 * @param string $string String to write
	 */
	public function writeLn(string $string): void
	{
		$this->write($string . PHP_EOL);
	}
	
	/**
	 * Dumps a value to the output.
	 *
	 * @param mixed $value  Value
	 */
	public function dump($value): void
	{
		$this->write(var_export($value, true) . PHP_EOL);
	}

	/**
	 * Clears the screen.
	 */
	public function clear(): void
	{
		if($this->environment->hasAnsiSupport())
		{
			$this->write("\e[H\e[2J");
		}
	}

	/**
	 * Clears the current line.
	 */
	public function clearLine(): void
	{
		if($this->environment->hasAnsiSupport())
		{
			$this->write("\r\33[2K");
		}
	}

	/**
	 * Do we have ANSI support?
	 *
	 * @return bool
	 */
	public function hasAnsiSupport(): bool
	{
		if($this->hasAnsiSupport === null)
		{
			$this->hasAnsiSupport = PHP_OS_FAMILY !== 'Windows' || (getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON');
		}
		
		return $this->hasAnsiSupport;
	}
}
