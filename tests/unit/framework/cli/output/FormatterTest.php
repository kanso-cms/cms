<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\cli\output;

use kanso\framework\cli\output\Formatter;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class FormatterTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicFormatter()
	{
		$formatter = new Formatter();

		$this->assertSame("\033[34mfoo\033[0m", $formatter->format('<blue>foo</blue>'));

		$this->assertSame("\033[34mfoo \033[32mbar\033[0m\033[34m baz\033[0m", $formatter->format('<blue>foo <green>bar</green> baz</blue>'));
	}

	/**
	 *
	 */
	public function testTagEscaping()
	{
		$formatter = new Formatter();

		$this->assertSame('<blue>foo</blue>', $formatter->format('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testCustomStyle()
	{
		$formatter = new Formatter();

		$formatter->addStyle('my_style', ['black', 'bg_green']);

		$this->assertSame("\033[30;42mfoo\033[0m", $formatter->format('<my_style>foo</my_style>'));
	}

	/**
	 *
	 */
	public function testEscape()
	{
		$formatter = new Formatter();

		$this->assertSame('\<blue>foo\</blue>', $formatter->escape('<blue>foo</blue>'));
	}

	/**
	 *
	 */
	public function testStripTags()
	{
		$formatter = new Formatter();

		$this->assertSame('foo', $formatter->stripTags('<blue>foo</blue>'));

		$this->assertSame('\<blue>foo\</blue>', $formatter->stripTags('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testStripSGR()
	{
		$formatter = new Formatter();

		$this->assertSame('foo', $formatter->stripSGR($formatter->format('<blue>foo</blue>')));
	}

	/**
	 *
	 */
	public function testUndefinedTagException()
	{
		$this->expectException(\Exception::class);

		$this->expectExceptionMessage('Undefined formatting tag [ fail ] detected.');

		$formatter = new Formatter();

		$formatter->format('<fail>hello</fail>');
	}

	/**
	 *
	 */
	public function testIncorrectTagNestingException()
	{
		$this->expectException(\Exception::class);

		$this->expectExceptionMessage('Detected incorrectly nested formatting tag.');

		$formatter = new Formatter();

		$formatter->format('<blue>he<green>llo</blue></green>');
	}

	/**
	 *
	 */
	public function testMissingCloseTagException()
	{
		$this->expectException(\Exception::class);

		$this->expectExceptionMessage('Detected missing formatting close tag');

		$formatter = new Formatter();

		$formatter->format('<blue>hello');
	}
}
