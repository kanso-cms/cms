<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\utility;

use kanso\framework\utility\Arr;
use kanso\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class fooArrayAccess
{
	public $test_key;

	public function __construct($var)
	{
		$this->test_key = $var;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 * @group framework
 */
class ArrTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$arr = [];

		Arr::set($arr, 'foo', '123');

		Arr::set($arr, 'bar.baz', '456');

		Arr::set($arr, 'bar.bax.0', '789');

		$this->assertEquals(['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]], $arr);
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertTrue(Arr::has($arr, 'foo'));

		$this->assertTrue(Arr::has($arr, 'bar.baz'));

		$this->assertTrue(Arr::has($arr, 'bar.bax.0'));

		$this->assertFalse(Arr::has($arr, 'bar.bax.1'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertEquals('123', Arr::get($arr, 'foo'));

		$this->assertEquals('456', Arr::get($arr, 'bar.baz'));

		$this->assertEquals('789', Arr::get($arr, 'bar.bax.0'));

		$this->assertEquals('abc', Arr::get($arr, 'bar.bax.1', 'abc'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
	 	$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertTrue(Arr::delete($arr, 'foo'));

		$this->assertTrue(Arr::delete($arr, 'bar.baz'));

		$this->assertTrue(Arr::delete($arr, 'bar.bax.0'));

		$this->assertFalse(Arr::delete($arr, 'nope.nope'));

		$this->assertEquals(['bar' => ['bax' => []]], $arr);
	}

	/**
	 *
	 */
	public function testPluck(): void
	{
	 	$arr = [['foo' => 'bar'], ['foo' => 'baz']];

	 	$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));

	 	//

	 	$obj1 = new \StdClass;

	 	$obj1->foo = 'bar';

	 	$obj2 = new \StdClass;

	 	$obj2->foo = 'baz';

	 	$arr = [$obj1, $obj2];

	 	$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));
	}

	/**
	 *
	 */
	public function testRandom(): void
	{
	 	$arr = ['foo', 'bar', 'baz'];

	 	$this->assertTrue(in_array(Arr::random($arr), $arr));
	}

	/**
	 *
	 */
	public function testIsAssoc(): void
	{
		$this->assertTrue(Arr::isAssoc(['foo' => 0, 'bar' => 1]));

		$this->assertFalse(Arr::isAssoc([0 => 'foo', 1 => 'bar']));

		$this->assertTrue(Arr::isAssoc(['foo' => 0, 1 => 'bar']));
	}

	/**
	 *
	 */
	public function testIsMulti(): void
	{
	 	$this->assertTrue(Arr::isMulti(['foo' => ['bar']]));

	 	$this->assertTrue(Arr::isMulti([0 => ['bar'], 1 => 'bar']));

		$this->assertTrue(Arr::isMulti(['foo' => 0, 1 => ['bar']]));

		$this->assertFalse(Arr::isMulti([0 => 'foo', 1 => 'bar']));
	}

	/**
	 *
	 */
	public function testInsertAt(): void
	{
		$sample1 = ['apple', 'orange', 'pear'];
		$result1 = ['apple', 'orange', 'inserted', 'pear'];

		$sample2 = [0 => 'apple', 'foo' => 'orange', 10 => 'pear'];
		$result2 = [0 => 'apple', 'foo' => 'orange', 'inserted', 10 => 'pear'];

		$sample3 = [0 => 'apple', 'foo' => 'orange', 10 => 'pear'];
		$result3 = [0 => 'apple', 'foo' => 'orange', 'foo' => 'bar', 10 => 'pear'];

		$this->assertEquals($result1, Arr::insertAt($sample1, 'inserted', 2));
		$this->assertEquals($result2, Arr::insertAt($sample2, 'inserted', 2));
		$this->assertEquals($sample3, Arr::insertAt($sample3, ['foo' => 'bar'], 2));
	}

	/**
	 *
	 */
	public function testIssets(): void
	{
		$this->assertEquals(true, Arr::issets(['needle1', 'needle2'], ['needle1' => 'foo', 'needle2' => 'foo', 'needle3' => 'foo']));

		$this->assertEquals(true, Arr::issets(['needle1', 'needle2'], ['needle1' => 'foo', 'needle2' => 'foo']));
	}

	/**
	 *
	 */
	public function testUnsets(): void
	{
		$this->assertEquals(['needle3' => 'foo'], Arr::unsets(['needle1', 'needle2'], ['needle1' => 'foo', 'needle2' => 'foo', 'needle3' => 'foo']));

		$this->assertEquals([], Arr::unsets(['needle1', 'needle2'], ['needle1' => 'foo', 'needle2' => 'foo']));
	}

	/**
	 *
	 */
	public function testSortMulti(): void
	{
		$this->assertEquals([['test_key' => 'a'], ['test_key' => 'b'], ['test_key' => 'c']], Arr::sortMulti([['test_key' => 'a'], ['test_key' => 'b'], ['test_key' => 'c']], 'test_key'));

		$this->assertEquals([['test_key' => 'c'], ['test_key' => 'b'], ['test_key' => 'a']], Arr::sortMulti([['test_key' => 'a'], ['test_key' => 'b'], ['test_key' => 'c']], 'test_key', 'DESC'));

		$this->assertEquals([['test_key' => 1], ['test_key' => 2], ['test_key' => 3]], Arr::sortMulti([['test_key' => 3], ['test_key' => 1], ['test_key' => 2]], 'test_key'));

		$this->assertEquals([['test_key' => 3], ['test_key' => 2], ['test_key' => 1]], Arr::sortMulti([['test_key' => 3], ['test_key' => 1], ['test_key' => 2]], 'test_key', 'DESC'));

		$this->assertEquals(['key2' => ['test_key' => 1], 'key3' => ['test_key' => 2], 'key1' => ['test_key' => 3]], Arr::sortMulti(['key1' => ['test_key' => 3], 'key2' => ['test_key' => 1], 'key3' => ['test_key' => 2]], 'test_key'));

		$this->assertEquals(['key1' => ['test_key' => 3], 'key3' => ['test_key' => 2], 'key2' => ['test_key' => 1]], Arr::sortMulti(['key1' => ['test_key' => 3], 'key2' => ['test_key' => 1], 'key3' => ['test_key' => 2]], 'test_key', 'DESC'));

		$this->assertEquals([new fooArrayAccess(1), new fooArrayAccess(2), new fooArrayAccess(3)], Arr::sortMulti([new fooArrayAccess(1), new fooArrayAccess(3), new fooArrayAccess(2)], 'test_key'));
	}

	/**
	 *
	 */
	public function testArrayAccess(): void
	{
		$this->assertEquals(1, Arr::arrayLikeAccess('test_key', new fooArrayAccess(1)));
	}

	/**
	 *
	 */
	public function testImplode(): void
	{
		$this->assertEquals('foobar', Arr::implodeByKey('test_key', [['test_key' => 'foo'], ['test_key' => 'bar']]));

		$this->assertEquals('foo+bar', Arr::implodeByKey('test_key', [['test_key' => 'foo'], ['test_key' => 'bar']], '+'));
	}

	/**
	 *
	 */
	public function testInMulti(): void
	{
		$this->assertEquals(true, Arr::inMulti('foobar', [['test_key' => 'foobar'], ['test_key' => 'bar']]));

		$this->assertEquals(false, Arr::inMulti('foobarz', [['test_key' => 'foobar'], ['test_key' => 'bar']]));

		$this->assertEquals(true, Arr::inMulti('foobar', [['test_key' => ['foobar']], ['test_key' => 'bar']]));
	}

	/**
	 *
	 */
	public function testPaginate(): void
	{
		$input = ['test1', 'test2', 'test3', 'test4', 'test5', 'test6'];
		$result =
		[
			['test1', 'test2'],
			['test3', 'test4'],
			['test5', 'test6'],
		];

		$this->assertEquals($result, Arr::paginate($input, 1, 2));

		$this->assertEquals(false, Arr::paginate($input, 10, 2));
	}
}
