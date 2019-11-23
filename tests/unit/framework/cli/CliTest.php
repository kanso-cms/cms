<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\cli;

use kanso\framework\cli\Cli;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class CliTest extends TestCase
{
	/**
	 *
	 */
	public function testCli()
	{
		$input  = Mockery::mock('\kanso\framework\cli\input\Input');
		$output = Mockery::mock('\kanso\framework\cli\output\Output');
		$env    = Mockery::mock('\kanso\framework\cli\Environment');
		$cli    = new Cli($input, $output, $env);

		$this->assertEquals($input, $cli->input());
		$this->assertEquals($output, $cli->output());
		$this->assertEquals($env, $cli->environment());
	}
}
