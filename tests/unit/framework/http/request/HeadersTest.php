<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\request;

use kanso\framework\http\request\Headers;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class HeadersTest extends TestCase
{
	/**
	 *
	 */
	public function getAcceptHeaders()
	{
		return
		[
			'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'HTTP_ACCEPT_CHARSET'  => 'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
			'HTTP_ACCEPT_ENCODING' => 'gzip,foobar;q=0.1,deflate,sdch',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
		];
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$headers = new Headers;

		$this->assertTrue($headers instanceof Headers);
	}

	/**
	 *
	 */
	public function testCountSet(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(1, count($headers->asArray()));
	}

	/**
	 *
	 */
	public function testIterateSet(): void
	{
		$headers    = new Headers(['FOO' => 'bar']);

		$iterations = 0;

		foreach($headers->asArray() as $header)
		{
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testReload(): void
	{
		$headers = new Headers;

		$headers->reload($this->getAcceptHeaders());

		$this->assertEquals('UTF-8,FOO-1; q=0.1,UTF-16;q=0.9', $headers->HTTP_ACCEPT_CHARSET);
	}

	/**
	 *
	 */
	public function testSet(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$headers->REQUEST_METHOD = 'POST';

		$this->assertEquals('POST', $headers->REQUEST_METHOD);
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$headers = new Headers(['foo' => 'bar']);

		$this->assertEquals('bar', $headers->asArray()['FOO']);
	}

	/**
	 *
	 */
	public function testUnset(): void
	{
		$headers = new Headers(['foo' => 'bar']);

		unset($headers->foo);

		$this->assertEquals(null, $headers->FOO);

		$this->assertSame(0, count($headers->asArray()));
	}

	/**
	 *
	 */
	public function testAsArray(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(['FOO' => 'bar'], $headers->asArray());
	}

	/**
	 *
	 */
	public function testIsset(): void
	{
		$headers = new Headers(['FOO' => 'bar', 'FOO_BAR' => 'foobar']);
		$this->assertTrue(isset($headers->FOO));
		$this->assertTrue(isset($headers->FOO_BAR));
		$this->assertTrue(isset($headers->foo_bar));
		$this->assertFalse(isset($headers->bar));
	}

	/**
	 *
	 */
	public function testAcceptableContentTypes(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'], $headers->acceptableContentTypes());
	}
	/**
	 *
	 */
	public function testAcceptableContentTypesWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableContentTypes('default'));
	}
	/**
	 *
	 */
	public function testAcceptableLanguages(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'], $headers->acceptableLanguages());
	}
	/**
	 *
	 */
	public function testAcceptableLanguagesWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableLanguages('default'));
	}
	/**
	 *
	 */
	public function testAcceptableCharsets(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['UTF-8', 'UTF-16', 'FOO-1'], $headers->acceptableCharsets());
	}
	/**
	 *
	 */
	public function acceptableCharsetsWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableCharsets('default'));
	}
	/**
	 *
	 */
	public function testAcceptableEncodings(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['gzip', 'deflate', 'sdch', 'foobar'], $headers->acceptableEncodings());
	}
	/**
	 *
	 */
	public function testAcceptableEncodingsWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableEncodings('default'));
	}
}
