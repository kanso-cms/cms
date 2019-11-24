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
class CommandTest extends TestCase
{
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

		$input->shouldReceive('subCommand')->once()->andReturn('bar');
		$input->shouldReceive('options')->once()->andReturn([]);
		$input->shouldReceive('parameters')->once()->andReturn([]);

		$output->shouldReceive('writeLn')->once()->with('<green>Success: The command was executed.</green>');

		$console->registerCommand('bar', '\kanso\tests\unit\framework\console\Foobar');

		$console->run();
	}
}

class Foobar extends Command
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
