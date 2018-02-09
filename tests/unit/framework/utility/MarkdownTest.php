<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\utility;

use tests\TestCase;
use kanso\framework\utility\Markdown;

/**
 * @group unit
 */
class MarkdownTest extends TestCase
{
	/**
	 *
	 */
	public function testMarkdown()
	{
		$this->assertEquals('<h1>Hello World!</h1>', Markdown::convert('# Hello World!'));
		$this->assertEquals('<h1>Hello World!</h1>', Markdown::convert('# Hello World!', false));
		$this->assertEquals('Hello World!', Markdown::plainText('# Hello World!'));
	}
}