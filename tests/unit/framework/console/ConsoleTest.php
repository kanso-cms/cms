<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\console;

use kanso\framework\cli\input\Input;
use kanso\framework\cli\output\Formatter;
use kanso\framework\cli\output\Output;
use kanso\framework\console\Command;
use kanso\framework\console\Console;
use kanso\framework\ioc\Container;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class ConsoleTest extends TestCase
{
	/**
	 *
	 */
	public function testNoCommand(): void
	{
		$container = Mockery::mock(Container::class);
		$input     = Mockery::mock(Input::class);
		$output    = Mockery::mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn(null);
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');
		$output->shouldReceive('write')->times(4)->with(PHP_EOL);
		$output->shouldReceive('write')->once()->with('php console [command] [arguments] [options]');
		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		$output->shouldReceive('formatter')->once()->andReturn($formatter);

		$output->shouldReceive('write')->once()->with(
'------------------------------
| <green>Command</green> | <green>Description</green>      |
------------------------------
| foo     | Foo description. |
------------------------------
');

		$console->registerCommand('foo', '\kanso\tests\unit\framework\console\Foo');

		$console->run();
	}

	/**
	 *
	 */
	public function testWrongCommand(): void
	{
		$container = Mockery::mock(Container::class);
		$input     = Mockery::mock(Input::class);
		$output    = Mockery::mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn('bar');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('write')->times(3)->with(PHP_EOL);
		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');
		$output->shouldReceive('write')->once()->with('<red>Unknown command [ bar ].</red>');

		$output->shouldReceive('formatter')->once()->andReturn($formatter);

		$output->shouldReceive('write')->once()->with(
'------------------------------
| <green>Command</green> | <green>Description</green>      |
------------------------------
| foo     | Foo description. |
------------------------------
');

		$console->registerCommand('foo', '\kanso\tests\unit\framework\console\Foo');

		$console->run();
	}

	/**
	 *
	 */
	public function testRunCommand(): void
	{
		$container = Mockery::mock(Container::class);
		$input     = Mockery::mock(Input::class);
		$output    = Mockery::mock(Output::class);
		$formatter = new Formatter;
		$console   = new Console($input, $output, $container);

		$input->shouldReceive('subCommand')->once()->andReturn('foo');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<green>Success: The command was executed.</green>');

		$console->registerCommand('foo', '\kanso\tests\unit\framework\console\Bar');

		$console->run();
	}
}

class Bar extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Foo description.';

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{
		$this->output->writeLn('<green>Success: The command was executed.</green>');
	}
}

class Foo extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Foo description.';

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{

	}
}
