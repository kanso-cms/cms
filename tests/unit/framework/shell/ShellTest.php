<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\utility;

use kanso\framework\shell\Shell;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class ShellTest extends TestCase
{
	/**
	 *
	 */
	public function testBuiltIn(): void
	{
		$cli = new Shell;

		$cli->cmd('cd ' . dirname(__FILE__))->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testIsSuccefull(): void
	{
		$cli = new Shell;

		$cli->cmd('cd ' . dirname(__FILE__))->run();

		$this->assertTrue($cli->is_successful());

		$cli->cmd('cfddfdsf ' . dirname(__FILE__))->run();

		$this->assertFalse($cli->is_successful());
	}

	/**
	 *
	 */
	public function testCustom(): void
	{
		$cli = new Shell;

		$cli->cmd('ruby')->option('v')->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testCd(): void
	{
		$cli = new Shell;

		$cli->cd(dirname(__FILE__))->cmd('ruby')->option('v')->run();

		$this->assertTrue($cli->is_successful());
	}

	/**
	 *
	 */
	public function testInputOutput(): void
	{
		$cli = new Shell;

		$input = dirname(__FILE__) . '/input.scss';

		$output = dirname(__FILE__) . '/output.css';

		$sass = "\$blue: #3bbfce;\n\nbody{\n\tcolor: \$blue\n}";

		$css = 'body{color:#3bbfce}';

		file_put_contents($input, $sass);

		$cli->cmd('sass')->input($input)->output($output)->option('style', 'compressed')->option('scss')->option('no-cache')->run();

		$this->assertTrue($cli->is_successful());

		$this->assertEquals($css, trim(file_get_contents($output)));

		unlink($input);

		unlink($output);
	}
}
