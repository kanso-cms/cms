<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\utility;

use tests\TestCase;
use kanso\framework\utility\Mime;

/**
 * @group unit
 */
class MimeTest extends TestCase
{
	/**
	 *
	 */
	public function testFromExt()
	{
		foreach (Mime::$mimeMap as $ext => $mime)
		{
			$ext = explode('|', $ext);
			$ext = $ext[0];
			$this->assertEquals($mime, Mime::fromExt($ext));
		}
	}

	/**
	 *
	 */
	public function testToExt()
	{
		foreach (Mime::$mimeMap as $ext => $mime)
		{
			$ext = explode('|', $ext);
			$ext = $ext[0];
			$this->assertEquals($ext, Mime::toExt($mime));
		}
	}
}