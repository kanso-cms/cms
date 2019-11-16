<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\console;

use kanso\framework\cli\input\Input;
use kanso\framework\cli\output\helpers\Table;
use kanso\framework\cli\output\Output;
use kanso\framework\ioc\Container;

/**
 * PHP Console.
 *
 * @author Joe J. Howard
 */
class Console
{
	/**
	 * Input.
	 *
	 * @var \kanso\framework\cli\input\Input
	 */
	private $input;

	/**
	 * Output.
	 *
	 * @var \kanso\framework\cli\output\Output
	 */
	private $output;

	/**
	 * Container.
	 *
	 * @var \kanso\framework\ioc\Container|null
	 */
	private $container;

	/**
	 * Commands.
	 *
	 * @var array
	 */
	private $commands = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \kanso\framework\cli\input\Input   $input     Input
	 * @param \kanso\framework\cli\output\Output $output    Output
	 * @param \kanso\framework\ioc\Container     $container Container instance (optional) (default null)
	 */
	public function __construct(Input $input, Output $output, Container $container = null)
	{
		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * Registers a command.
	 *
	 * @access public
	 * @param string $command Command
	 * @param string $class   Command class
	 */
	public function registerCommand(string $command, string $class): void
	{
		$this->commands[$command] = $class;
	}

	/**
	 * Run the console.
	 *
	 * @access public
	 */
	public function run()
	{
		$command = $this->input->subCommand();
		$options = $this->input->options();
		$params  = $this->input->parameters();

		// No sub command provided
		if (is_null($command))
		{
			return $this->displayConsoleInfoAndCommandList();
		}

		// Command does not exist
		if($this->commandExists($command) === false)
		{
			return $this->unknownCommand($command);
		}

		// Help on command
		if(in_array('help', $options) || in_array('h', $options))
		{
			return $this->displayCommandHelp($command);
		}

		$commandInstance = $this->commandInstance($command);

		$commandInstance->execute();
	}

	/**
	 * Draws information table.
	 *
	 * @access private
	 * @param string $heading Table heading
	 * @param array  $headers Table headers
	 * @param array  $rows    Table rows
	 */
	private function drawTable(string $heading, array $headers, array $rows): void
	{
		if(!empty($rows))
		{
			$this->output->write(PHP_EOL);

			$this->output->write(PHP_EOL);

			$this->output->writeLn("<yellow>{$heading}</yellow>");

			$this->output->write(PHP_EOL);

			$table = new Table($this->output);

			$headers = array_map(function($value) { return "<green>{$value}</green>"; }, $headers);

			$this->output->write($table->render($headers, $rows));
		}
	}

	/**
	 * Draws an argument table.
	 *
	 * @access private
	 * @param string $heading   Table heading
	 * @param array  $arguments Arguments
	 */
	private function drawArgumentTable(string $heading, array $arguments): void
	{
		$this->drawTable($heading, ['Name', 'Description', 'Optional'], $arguments);
	}

	/**
	 * Displays basic console information.
	 *
	 * @access private
	 */
	private function displayConsoleInfo(): void
	{
		// Display basic console information

		$this->output->writeLn('<yellow>Usage:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->write('php console [command] [arguments] [options]');
	}

	/**
	 * Returns an array of command information.
	 *
	 * @access private
	 * @return array
	 */
	private function getCommands(): array
	{
		$info = [];

		foreach($this->commands as $name => $class)
		{
			$command = new $class($this->input, $this->output);

			$info[$name] = [$name, $command->getDescription()];
		}

		ksort($info);

		return $info;
	}

	/**
	 * Lists available commands if there are any.
	 *
	 * @access private
	 */
	private function listCommands(): void
	{
		$commands = $this->getCommands();

		$this->drawTable('Available commands:', ['Command', 'Description'], $commands);
	}

	/**
	 * Displays console info and lists all available commands.
	 *
	 * @access private
	 */
	private function displayConsoleInfoAndCommandList()
	{
		$this->displayConsoleInfo();

		$this->listCommands();
	}

	/**
	 * Returns TRUE if the command exists and FALSE if not.
	 *
	 * @access private
	 * @param  string $command Command
	 * @return bool
	 */
	private function commandExists(string $command): bool
	{
		return isset($this->commands[$command]);
	}

	/**
	 * Displays error message for unknown commands.
	 *
	 * @access private
	 * @param string $command Command
	 */
	private function unknownCommand(string $command)
	{
		$message = "Unknown command [ {$command} ].";

		$this->output->write("<red>{$message}</red>");

		$this->listCommands();
	}

	/**
	 * Construct a command instance by name.
	 *
	 * @access private
	 * @param  string $command Command
	 * @return mixed
	 */
	private function commandInstance(string $command)
	{
		$class = $this->commands[$command];

		return new $class($this->input, $this->output, $this->container);
	}

	/**
	 * Displays information about the chosen command.
	 *
	 * @access private
	 * @param string $command Command
	 */
	private function displayCommandHelp(string $command)
	{
		$commandInstance = $this->commandInstance($command);

		$this->output->writeLn('<yellow>Command:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn("php console {$command}");

		$this->output->write(PHP_EOL);

		$this->output->writeLn('<yellow>Description:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn($commandInstance->getDescription());

		$this->drawArgumentTable('Arguments and options:', $commandInstance->getArguments());
	}
}