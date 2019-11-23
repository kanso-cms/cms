<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\cli\input;

use kanso\framework\cli\input\Input;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class InputTest extends TestCase
{
	/**
	 *
	 */
	private function inputArgs()
	{
		return
		[
			'command',
			'sub-command',
			'--foo=bar',
			'--bar',
			'foo',
			'-fbs',
			'--fooz',
			'-l=bar',
			'-b'
		];
	}

	/**
	 *
	 */
	private function inputParams()
	{
		return
		[
			'foo' => 'bar',
			'bar' => 'foo',
			'l'   => 'bar'
		];
	}

	/**
	 *
	 */
	private function inputOptions()
	{
		return
		[
			'f',
			'b',
			's',
			'fooz',
			'b'
		];
	}

	/**
	 *
	 */
	public function testCommand()
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals('command', $input->command());
	}

	/**
	 *
	 */
	public function testSubCommand()
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals('sub-command', $input->subCommand());
	}

	/**
	 *
	 */
	public function testParameters()
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals($this->inputParams(), $input->parameters());
	}

	/**
	 *
	 */
	public function testOptions()
	{
		$input= new Input($this->inputArgs());

		$this->assertEquals($this->inputOptions(), $input->options());
	}
}
