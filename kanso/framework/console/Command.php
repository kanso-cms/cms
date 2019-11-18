<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\console;

use kanso\framework\cli\input\Input;
use kanso\framework\cli\output\Output;
use kanso\framework\ioc\Container;

/**
 * Database manager.
 *
 * @author Joe J. Howard
 */
abstract class Command
{
	/**
	 * Input.
	 *
	 * @var \kanso\framework\cli\input\Input
	 */
	protected $input;

	/**
	 * Output.
	 *
	 * @var \kanso\framework\cli\output\Output
	 */
	protected $output;

	/**
	 * Container.
	 *
	 * @var \kanso\framework\ioc\Container|null
	 */
	protected $container;

	/**
	 * Command description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Available params.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Available options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Constructor.
	 *
	 * @param \kanso\framework\cli\input\Input   $input     Input
	 * @param \kanso\framework\cli\output\Output $output    Output
	 * @param \kanso\framework\ioc\Container     $container Container instance (optional) (default null)
	 */
	public function __construct(Input $input, Output $output, Container $container = null)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return array_merge($this->options, $this->params);
	}

	/**
	 * Executes the command.
	 */
	abstract public function execute();

}
