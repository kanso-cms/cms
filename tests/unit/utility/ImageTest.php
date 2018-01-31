<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\utility;

use tests\TestCase;
use kanso\framework\utility\Image;

/**
 * @group unit
 */
class ImageTest extends TestCase
{
	/**
	 * 
	 */
	public function testConstructor()
	{
		$this->assertTrue($this->imageInput() instanceof Image);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorWithInvalidFile()
	{
		$image = new Image(__FILE__);
	}

	/**
	 *
	 */
	public function testWidth()
	{
		$this->assertEquals(114, $this->imageInput()->width());
	}

	/**
	 *
	 */
	public function testHeight()
	{
		$this->assertEquals(114, $this->imageInput()->height());
	}

	/**
	 *
	 */
	public function testResizeToHeight()
	{
		$this->assertEquals(300, $this->imageInput()->resizeToHeight(300, true)->height());
		$this->assertEquals(114, $this->imageInput()->resizeToHeight(300)->height());
	}

	/**
	 *
	 */
	public function testResizeToWidth()
	{
		$this->assertEquals(300, $this->imageInput()->resizeToWidth(300, true)->height());
		$this->assertEquals(114, $this->imageInput()->resizeToWidth(300)->height());
	}

	/**
	 *
	 */
	public function testScale()
	{
		$this->assertEquals(228, $this->imageInput()->scale(200)->height());
	}

	/**
	 *
	 */
	public function testResize()
	{
		$this->assertEquals(300, $this->imageInput()->resize(100, 300, true)->height());
		$this->assertEquals(114, $this->imageInput()->resize(100, 300, false)->height());
	}

	/**
	 *
	 */
	public function testCrop()
	{
		$this->assertEquals(70, $this->imageInput()->crop(70, 70)->height());
		$this->assertEquals(114, $this->imageInput()->crop(300, 300)->height());
		$this->assertEquals(300, $this->imageInput()->crop(300, 300, true)->height());
	}

	/**
	 *
	 */
	public function testSave()
	{
		# Create and save png
		$image   = $this->imageInput()->resizeToWidth(100);
		$pngPath = $this->imageOutputPngPath();
		$image->save($pngPath);
		$this->assertEquals(100, $this->imageOutputPng()->width());
		unlink($pngPath);

		# Create and save jpg
		$image   = $this->imageInput()->resizeToWidth(100);
		$jpgPath = $this->imageOutputJpgPath();
		$image->save($jpgPath);
		$this->assertEquals(100, $this->imageOutputJpg()->width());
		unlink($jpgPath);

		# Create and save gif
		$image   = $this->imageInput()->resizeToWidth(100);
		$gifPath = $this->imageOutputGifPath();
		$image->save($gifPath);
		$this->assertEquals(100, $this->imageOutputGif()->width());
		unlink($gifPath);
	}

	/**
	 * 
	 */
	private function imageInput()
	{
		return new Image($this->getRootDir().'/kanso/cms/admin/assets/images/apple-touch-icon-114x114.png');
	}

	/**
	 * 
	 */
	private function imageOutputPng()
	{
		return new Image($this->imageOutputPngPath());
	}

	/**
	 * 
	 */
	private function imageOutputPngPath()
	{
		return dirname(__FILE__).'/test.png';
	}

	/**
	 * 
	 */
	private function imageOutputJpg()
	{
		return new Image($this->imageOutputJpgPath());
	}

	/**
	 * 
	 */
	private function imageOutputJpgPath()
	{
		return dirname(__FILE__).'/test.jpg';
	}

	/**
	 * 
	 */
	private function imageOutputGif()
	{
		return new Image($this->imageOutputGifPath());
	}

	/**
	 * 
	 */
	private function imageOutputGifPath()
	{
		return dirname(__FILE__).'/test.gif';
	}

	/**
	 * 
	 */
	private function getRootDir()
	{
		return dirname(dirname(dirname(dirname(__FILE__))));
	}
}