<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\file;

use kanso\framework\file\CascadingFilesystem;
use kanso\tests\TestCase;

/**
 * Cascading file loader.
 */
class Loader
{
    use CascadingFilesystem;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
	}
}

/**
 * @group unit
 * @group framework
 */
class CascadingFilesystemTest extends TestCase
{
	/**
	 *
	 */
	public function testGetFilePath()
	{
		$loader = new Loader(dirname(__FILE__));

		$file = substr(__FILE__, strrpos(__FILE__, '/') + 1);

		$file = substr($file, 0, strrpos($file, '.'));

		$this->assertEquals(__FILE__, $loader->getFilePath($file));
	}
}
