<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\pixl;

use kanso\framework\pixl\Image;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class ImageTest extends TestCase
{
	/**
	 *
	 */
	public function getProcessor()
	{
		return Mockery::mock('\kanso\framework\pixl\processor\ProcessorInterface');
	}

	/**
	 *
	 */
	public function testConstructor()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$image = new Image(__FILE__, $processor);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testConstructorWithNonExistingFile()
	{
		$processor = $this->getProcessor();

		$image = new Image('foobar.png', $processor);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testSave()
	{
		$processor = $this->getProcessor();

		$image = new Image('foobar.png', $processor);

		$image->save();
	}

	/**
	 *
	 */
	public function testResizeToPixelSize()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, false)->once();

		$image = new Image(__FILE__, $processor);

		$image->resize(300, 300);
	}

	/**
	 *
	 */
	public function testResizeToPixelWithoutRestriction()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, true)->once();

		$image = new Image(__FILE__, $processor);

		$image->resize(300, 300, true);
	}

	/**
	 *
	 */
	public function testCrop()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, false)->once();

		$image = new Image(__FILE__, $processor);

		$image->crop(300, 300);
	}

	/**
	 *
	 */
	public function testCropWithEnlarge()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, true)->once();

		$image = new Image(__FILE__, $processor);

		$image->crop(300, 300, true);
	}

	/**
	 *
	 */
	public function testGetWidth()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('width')->once()->andReturn(10);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(10, $image->width());
	}

	/**
	 *
	 */
	public function testGetHeight()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('height')->once()->andReturn(10);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(10, $image->height());
	}
}
