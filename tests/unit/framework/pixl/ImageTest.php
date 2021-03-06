<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\pixl;

use kanso\framework\pixl\Image;
use kanso\tests\TestCase;

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
		return $this->mock('\kanso\framework\pixl\processor\ProcessorInterface');
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$image = new Image($processor, __FILE__);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testConstructorWithNonExistingFile(): void
	{
		$processor = $this->getProcessor();

		$image = new Image($processor, 'foobar.png');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testSave(): void
	{
		$processor = $this->getProcessor();

		$image = new Image($processor, 'foobar.png');

		$image->save();
	}

	/**
	 *
	 */
	public function testResizeToPixelSize(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, false)->once();

		$image = new Image($processor, __FILE__);

		$image->resize(300, 300);
	}

	/**
	 *
	 */
	public function testResizeToPixelWithoutRestriction(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, true)->once();

		$image = new Image($processor, __FILE__);

		$image->resize(300, 300, true);
	}

	/**
	 *
	 */
	public function testCrop(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, false)->once();

		$image = new Image($processor, __FILE__);

		$image->crop(300, 300);
	}

	/**
	 *
	 */
	public function testCropWithEnlarge(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, true)->once();

		$image = new Image($processor, __FILE__);

		$image->crop(300, 300, true);
	}

	/**
	 *
	 */
	public function testGetWidth(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('width')->once()->andReturn(10);

		$image = new Image($processor, __FILE__);

		$this->assertSame(10, $image->width());
	}

	/**
	 *
	 */
	public function testGetHeight(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('height')->once()->andReturn(10);

		$image = new Image($processor, __FILE__);

		$this->assertSame(10, $image->height());
	}
}
