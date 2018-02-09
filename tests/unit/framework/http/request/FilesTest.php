<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\response;

use tests\TestCase;
use kanso\framework\http\request\Files;

/**
 * @group unit
 */
class FilesTest extends TestCase
{
	/**
	 *
	 */
	protected function getSingleUpload(): array
	{
		return
		[
			'upload' =>
			[
				'name'     => 'foo',
				'tmp_name' => '/tmp/qwerty',
				'type'     => 'foo/bar',
				'size'     => 123,
				'error'    => 0,
			],
		];
	}
	/**
	 *
	 */
	protected function getMultiUpload()
	{
		return
		[
			'upload' =>
			[
				'name'     => ['foo', 'bar'],
				'tmp_name' => ['/tmp/qwerty', '/tmp/azerty'],
				'type'     => ['foo/bar', 'foo/bar'],
				'size'     => [123, 456],
				'error'    => [0, 0],
			],
		];
	}
	/**
	 *
	 */
	public function testCountSet()
	{
		$files = new Files($this->getSingleUpload());
		
		$this->assertSame(1, count($files->asArray()));
		
		$files = new Files($this->getMultiUpload());
		
		$this->assertSame(1, count($files->asArray()));
	}
	/**
	 *
	 */
	public function testAdd()
	{
		$files = new Files;
		
		$files->put('upload', $this->getSingleUpload()['upload']);
		
		$this->assertTrue(is_array($files->get('upload')) && !empty($files->get('upload')));
	}
	/**
	 *
	 */
	public function testGet()
	{
		$files = new Files($this->getSingleUpload());
		
		$this->assertTrue(is_array($files->get('upload')) && !empty($files->get('upload')));
		
		$files = new Files($this->getMultiUpload());
		
		$this->assertTrue(is_array($files->get('upload.0')) && !empty($files->get('upload.0')));
		
		$this->assertTrue(is_array($files->get('upload.1')) && !empty($files->get('upload.1')));
	}

}