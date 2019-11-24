<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\session;

use kanso\framework\http\session\Flash;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class FlashTest extends TestCase
{
    /**
     *
     */
    public function testDefault(): void
    {
        $flash = new Flash;

        $this->assertEquals([], $flash->get());
    }

    /**
     *
     */
    public function testSet(): void
    {
        $flash = new Flash;

        $flash->put('foo', 'bar');

        $this->assertEquals('bar', $flash->get('foo'));
    }

    /**
     *
     */
    public function testSetMultiple(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $this->assertEquals('bar', $flash->get('foo'));
        $this->assertEquals('foo', $flash->get('bar'));
    }

    /**
     *
     */
    public function testGet(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $this->assertEquals('bar', $flash->get('foo'));
        $this->assertEquals('foo', $flash->get('bar'));
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $flash->get());
    }

    /**
     *
     */
    public function testHas(): void
    {
        $flash = new Flash;

        $this->assertFalse($flash->has('foo'));

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $this->assertTrue($flash->has('foo'));
        $this->assertTrue($flash->has('bar'));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $this->assertTrue($flash->has('foo'));
        $this->assertTrue($flash->has('bar'));

        $flash->remove('foo');
        $flash->remove('bar');

        $this->assertFalse($flash->has('foo'));
        $this->assertFalse($flash->has('bar'));
    }

    /**
     *
     */
    public function testClear(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $flash->clear();

        $this->assertEquals([], $flash->get());
    }

    /**
     *
     */
    public function testExpire(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $flash->iterate();

        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $flash->get());

        $flash->iterate();

        $this->assertEquals([], $flash->get());
    }

    /**
     *
     */
    public function testPutRaw(): void
    {
        $raw =
        [
            'foo' => ['key' => 'bar', 'count' => 0],
            'bar' => ['key' => 'foo', 'count' => 0],
        ];

        $flash = new Flash;

        $flash->putRaw($raw);

        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $flash->get());

        $flash->clear();

        $flash->putRaw(['foo' => 'bar']);

        $this->assertEquals([], $flash->get());
    }

    /**
     *
     */
    public function testGetRaw(): void
    {
        $raw =
        [
            'foo' => ['key' => 'bar', 'count' => 0],
            'bar' => ['key' => 'foo', 'count' => 0],
        ];

        $flash = new Flash;

        $flash->putRaw($raw);

        $this->assertEquals($raw, $flash->getRaw());
    }

    /**
     *
     */
    public function testIteration(): void
    {
        $flash = new Flash;

        $flash->putMultiple([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $i = 0;
        foreach ($flash as $k => $v)
        {
            if ($i === 0)
            {
                $this->assertEquals('foo', $k);
                $this->assertEquals('bar', $v);
            }
            else
            {
                $this->assertEquals('bar', $k);
                $this->assertEquals('foo', $v);
            }
            $i++;
        }
    }
}
