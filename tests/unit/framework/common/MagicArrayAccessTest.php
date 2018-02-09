<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\common;

use tests\TestCase;
use kanso\framework\common\MagicArrayAccessTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestMagicArrayAccess
{
    use MagicArrayAccessTrait;

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
 */
class MagicArrayAccessTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
        $arrayAccess = new TestMagicArrayAccess(['foo' => 'bar']);

        $this->assertEquals('bar', $arrayAccess->foo);
    }

    /**
     *
     */
    public function testSetGet()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = 'baz';

        $this->assertEquals('baz', $arrayAccess->foo);
    }

    /**
     *
     */
    public function testSetGetNested()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->foo);
    }

    /**
     *
     */
    public function testHas()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = 'baz';

        $this->assertTrue(isset($arrayAccess->foo));

        $this->assertFalse(isset($arrayAccess->baz));
    }

    /**
     *
     */
    public function testHasNested()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertTrue(isset($arrayAccess->foo));

        $this->assertTrue(isset($arrayAccess->foo['bar']));

        $this->assertFalse(isset($arrayAccess->baz));

        $this->assertFalse(isset($arrayAccess->foo['baz']));
    }

    /**
     *
     */
    public function testRemove()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = ['bar' => 'baz'];

        unset($arrayAccess->foo);

        $this->assertEquals(null, $arrayAccess->foo);
    }

    /**
     *
     */
    public function testAsArray()
    {
        $arrayAccess = new TestMagicArrayAccess;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertEquals(['foo' => ['bar' => 'baz']], $arrayAccess->asArray());
    }
}
