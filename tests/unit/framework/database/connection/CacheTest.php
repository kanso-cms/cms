<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\database\connection;

use kanso\framework\database\connection\Cache;
use tests\TestCase;

/**
 * @group unit
 */
class CacheTest extends TestCase
{
    /**
     *
     */
    public function testSet()
    {
    	$cache = new Cache;

    	$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$cache->put(['foo' => 'bar', 'foo' => 'baz']);

		$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$this->assertEquals(['foo' => 'bar', 'foo' => 'baz'], $cache->get());
    }

    /**
     *
     */
    public function testGet()
    {
    	$cache = new Cache;

    	$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$cache->put(['foo' => 'bar', 'foo' => 'baz']);

		$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$this->assertEquals(['foo' => 'bar', 'foo' => 'baz'], $cache->get());
    }

    /**
     *
     */
    public function testHas()
    {
    	$cache = new Cache;

    	$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$cache->put(['foo' => 'bar', 'foo' => 'baz']);

		$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$this->assertTrue($cache->has());

		$cache->setQuery('SELECT * FROM prefixed_foobar_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$this->assertFalse($cache->has());
    }

    /**
     *
     */
    public function testClear()
    {
    	$cache = new Cache;

    	$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$cache->put(['foo' => 'bar', 'foo' => 'baz']);

		$cache->clear();

		$this->assertFalse($cache->has());
    }

    /**
     *
     */
    public function testDisable()
    {
    	$cache = new Cache;

    	$cache->setQuery('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

		$cache->put(['foo' => 'bar', 'foo' => 'baz']);

		$cache->disable();

		$this->assertFalse($cache->has());
    }
}
