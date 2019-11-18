<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli;

use kanso\framework\cli\input\Input;
use kanso\framework\cli\output\Output;

/**
 * CLI utility.
 *
 * @author Joe J. Howard
 */
class CLI
{
	/**
	 * Input instance.
	 *
	 * @var \kanso\framework\cli\input\Input
	 */
	private $input;

	/**
	 * Output instance.
	 *
	 * @var \kanso\framework\cli\output\Output
	 */
	private $output;

    /**
     * Environment instance.
     *
     * @var \kanso\framework\cli\Environment
     */
    private $environment;

    /**
     * Constructor.
     *
     * @param \kanso\framework\cli\input\Input   $input       Input instance
     * @param \kanso\framework\cli\output\Output $output      Output instance
     * @param \kanso\framework\cli\Environment   $environment Environment instance
     */
    public function __construct(Input $input, Output $output, Environment $environment)
    {
        $this->input = $input;

        $this->output = $output;

        $this->environment = $environment;
    }

    /**
     * Returns the input.
     *
     * @return \kanso\framework\cli\input\Input
     */
    public function input(): Input
    {
        return $this->input;
    }

    /**
     * Returns the output.
     *
     * @return \kanso\framework\cli\output\Output
     */
    public function output(): Output
    {
        return $this->input;
    }

    /**
     * Returns the input.
     *
     * @return \kanso\framework\cli\Environment
     */
    public function environment(): environment
    {
        return $this->environment;
    }
}
