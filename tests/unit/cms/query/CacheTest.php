<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\query;

use kanso\cms\query\Cache;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CacheTest extends TestCase
{
    /**
     *
     */
    public function testSetGet()
    {
        $cache = new Cache;

        $cache->set('foo', 'baz');

        $this->assertEquals('baz', $cache->get('foo'));
    }

    /**
     *
     */
    public function testSetGetNested()
    {
        $cache = new Cache;

        $cache->set('foo', ['bar' => 'baz']);

        $this->assertEquals(['bar' => 'baz'], $cache->get('foo'));

        $cache = new Cache;

        $cache->set('foo.bar', 'baz');

        $this->assertEquals('baz', $cache->get('foo.bar'));
    }

    /**
     *
     */
    public function testSetGetMultiple()
    {
        $cache = new Cache;

        $cache->setMultiple(['foo' => ['bar' => 'baz']]);

        $this->assertEquals(['bar' => 'baz'], $cache->get('foo'));

        $cache->set('foo.bar', 'foobaz');

        $this->assertEquals('foobaz', $cache->get('foo.bar'));
    }

    /**
     *
     */
    public function testHas()
    {
        $cache = new Cache;

        $cache->set('foo', 'baz');

        $this->assertTrue($cache->has('foo'));

        $this->assertFalse($cache->has('baz'));
    }

    /**
     *
     */
    public function testHasNested()
    {
        $cache = new Cache;

        $cache->set('foo', ['bar' => 'baz']);

        $this->assertTrue($cache->has('foo'));

        $this->assertTrue($cache->has('foo.bar'));

        $this->assertFalse($cache->has('baz'));

        $this->assertFalse($cache->has('foo.baz'));
    }

    /**
     *
     */
    public function testRemove()
    {
        $cache = new Cache;

        $cache->set('foo', ['bar' => 'baz']);

        $cache->remove('foo.bar');

        $cache->remove('foo');

        $this->assertEquals(null, $cache->get('foo'));
    }

    /**
     *
     */
    public function testClear()
    {
        $cache = new Cache;

        $cache->set('foo', ['bar' => 'baz']);

        $cache->clear();

        $this->assertFalse($cache->has('foo'));
    }

    /**
     *
     */
    public function testAsArray()
    {
        $cache = new Cache;

        $cache->set('foo', ['bar' => 'baz']);

        $this->assertEquals(['foo' => ['bar' => 'baz']], $cache->asArray());

        $this->assertEquals(['foo' => ['bar' => 'baz']], $cache->get());
    }

    /**
     *
     */
    public function testKey()
    {
        $cache = new Cache;

        $key1 = $cache->key('foobar_func', ['arg1', 'arg2', 'arg3'], 3);

        $key2 = $cache->key('foobar_func', ['arg1', 'arg2'], 2);

        $cache->set($key1, 'foobar');

        $this->assertEquals('foobar', $cache->get($key1));

        $this->assertFalse($cache->has($key2));
    }
}
