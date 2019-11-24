<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\cli\output;

use kanso\framework\cli\output\Output;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class OutputTest extends TestCase
{
	/**
	 *
	 */
	public function getFormatter()
	{
		return Mockery::mock('\kanso\framework\cli\output\Formatter');
	}

	/**
	 *
	 */
	public function getEnvironment()
	{
		return Mockery::mock('\kanso\framework\cli\Environment');
	}

	/**
	 *
	 */
	public function getOutputBuffer()
	{
		$handle = tmpfile();
        $path   = stream_get_meta_data($handle)['uri'];
        fclose($handle);

       	return $path;
	}

	/**
	 *
	 */
	public function testGetEnvironment(): void
	{
		$formatter   = $this->getFormatter();
		$environment = $this->getEnvironment();
		$output      = new Output($formatter, $environment);
		$this->assertSame($environment, $output->environment());
	}

	/**
	 *
	 */
	public function testWrite(): void
	{
		$path        = $this->getOutputBuffer();
        $handle      = fopen($path, 'w');
		$formatter   = $this->getFormatter();
		$environment = $this->getEnvironment();
		$output      = new Output($formatter, $environment, $handle);

		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('hello, world!');
		$environment->shouldReceive('hasAnsiSupport')->once()->andReturn(true);

		$output->write('hello, world!');

		$this->assertSame('hello, world!', file_get_contents($path));
	}

	/**
	 *
	 */
	public function testWriteLn(): void
	{
		$path        = $this->getOutputBuffer();
        $handle      = fopen($path, 'w');
		$formatter   = $this->getFormatter();
		$environment = $this->getEnvironment();
		$output      = new Output($formatter, $environment, $handle);
		$response    = 'hello, world!' . PHP_EOL;

		$formatter->shouldReceive('format')->once()->with($response)->andReturn($response);
		$environment->shouldReceive('hasAnsiSupport')->once()->andReturn(true);

		$output->writeLn('hello, world!');

		$this->assertSame($response, file_get_contents($path));
	}

	/**
	 *
	 */
	public function testDump(): void
	{
		$path        = $this->getOutputBuffer();
        $handle      = fopen($path, 'w');
		$formatter   = $this->getFormatter();
		$environment = $this->getEnvironment();
		$output      = new Output($formatter, $environment, $handle);
		$response    = var_export('hello, world!', true) . PHP_EOL;

		$formatter->shouldReceive('format')->once()->with($response)->andReturn($response);
		$environment->shouldReceive('hasAnsiSupport')->once()->andReturn(true);

		$output->dump('hello, world!');

		$this->assertSame($response, file_get_contents($path));
	}

	/**
	 *
	 */
	public function testWriteNoAnsiSupport(): void
	{
		$path        = $this->getOutputBuffer();
        $handle      = fopen($path, 'w');
		$formatter   = $this->getFormatter();
		$environment = $this->getEnvironment();
		$output      = new Output($formatter, $environment, $handle);

		$formatter->shouldReceive('stripTags')->once()->with('<red>hello, world!</red>')->andReturn('hello, world!');
		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('hello, world!');
		$environment->shouldReceive('hasAnsiSupport')->once()->andReturn(false);

		$output->write('<red>hello, world!</red>');

		$this->assertSame('hello, world!', file_get_contents($path));
	}
}
