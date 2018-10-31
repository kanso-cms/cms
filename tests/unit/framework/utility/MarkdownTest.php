<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\utility;

use kanso\framework\utility\Markdown;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
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
