<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\common;

use kanso\framework\common\ArrayAccessTrait;
use kanso\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestArrayAccess
{
    use ArrayAccessTrait;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 * @group framework
 */
class ArrayAccessTraitTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
        $arrayAccess = new TestArrayAccess(['foo' => 'bar']);

        $this->assertEquals('bar', $arrayAccess->get('foo'));
    }

    /**
     *
     */
    public function testSetGet()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', 'baz');

        $this->assertEquals('baz', $arrayAccess->get('foo'));
    }

    /**
     *
     */
    public function testSetGetNested()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->get('foo'));

        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo.bar', 'baz');

        $this->assertEquals('baz', $arrayAccess->get('foo.bar'));
    }

    /**
     *
     */
    public function testSetGetMultiple()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->setMultiple(['foo' => ['bar' => 'baz']]);

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->get('foo'));

        $arrayAccess->set('foo.bar', 'foobaz');

        $this->assertEquals('foobaz', $arrayAccess->get('foo.bar'));
    }

    /**
     *
     */
    public function testHas()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', 'baz');

        $this->assertTrue($arrayAccess->has('foo'));

        $this->assertFalse($arrayAccess->has('baz'));
    }

    /**
     *
     */
    public function testHasNested()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $this->assertTrue($arrayAccess->has('foo'));

        $this->assertTrue($arrayAccess->has('foo.bar'));

        $this->assertFalse($arrayAccess->has('baz'));

        $this->assertFalse($arrayAccess->has('foo.baz'));
    }

    /**
     *
     */
    public function testRemove()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $arrayAccess->remove('foo.bar');

        $arrayAccess->remove('foo');

        $this->assertEquals(null, $arrayAccess->get('foo'));
    }

    /**
     *
     */
    public function testClear()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $arrayAccess->clear();

        $this->assertFalse($arrayAccess->has('foo'));
    }

    /**
     *
     */
    public function testAsArray()
    {
        $arrayAccess = new TestArrayAccess;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $this->assertEquals(['foo' => ['bar' => 'baz']], $arrayAccess->asArray());

        $this->assertEquals(['foo' => ['bar' => 'baz']], $arrayAccess->get());
    }
}
