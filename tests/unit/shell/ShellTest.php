<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\utility;

use tests\TestCase;
use kanso\framework\shell\Shell;

/**
 * @group unit
 */
class ShellTest extends TestCase
{
	/**
	 *
	 */
	public function testBuiltIn()
	{
		$cli = new Shell;

		$cli->cmd('cd '.dirname(__FILE__))->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testIsSuccefull()
	{
		$cli = new Shell;

		$cli->cmd('cd '.dirname(__FILE__))->run();

		$this->assertTrue($cli->is_successful());

		$cli->cmd('cfddfdsf '.dirname(__FILE__))->run();

		$this->assertFalse($cli->is_successful());
	}

	/**
	 *
	 */
	public function testCustom()
	{
		$cli = new Shell;

		$cli->cmd('ruby')->option('v')->run();

		$this->assertTrue($cli->is_successful());
	}
}