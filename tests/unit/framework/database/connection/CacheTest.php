<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\database\connection;

use kanso\framework\database\connection\Cache;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class CacheTest extends TestCase
{
    /**
     *
     */
    public function testConstructorEnabled(): void
    {
        $cache = new Cache(true);

        $this->assertTrue($cache->enabled());
    }

    /**
     *
     */
    public function testConstructordisabled(): void
    {
        $cache = new Cache(false);

        $this->assertFalse($cache->enabled());
    }

    /**
     *
     */
    public function testEnabledDisabled(): void
    {
        $cache = new Cache;

        $cache->enable();

        $this->assertTrue($cache->enabled());

        $cache->disable();

        $this->assertFalse($cache->enabled());
    }

    /**
     *
     */
    public function testGetTrue(): void
    {
    	$cache  = new Cache;
        $query  = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';
        $params = ['column_key' => 'value'];
        $result = ['foo' => 'bar', 'foo' => 'baz'];

    	$cache->put($query, $params, $result);

		$this->assertEquals($result, $cache->get($query, $params));
    }

    /**
     *
     */
    public function testGetFalse(): void
    {
        $cache  = new Cache;
        $query  = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';
        $params = ['column_key' => 'value'];
        $result = ['foo' => 'bar', 'foo' => 'baz'];

        $cache->put($query, $params, $result);

        $this->assertFalse($cache->get($query, ['column_key' => 'valu2']));
    }

    /**
     *
     */
    public function testHasTrue(): void
    {
        $cache  = new Cache;
        $query  = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';
        $params = ['column_key' => 'value'];
        $result = ['foo' => 'bar', 'foo' => 'baz'];

        $cache->put($query, $params, $result);

        $this->assertTrue($cache->has($query, $params));
    }

    /**
     *
     */
    public function testHasFalse(): void
    {
        $cache  = new Cache;
        $query  = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';
        $params = ['column_key' => 'value'];
        $result = ['foo' => 'bar', 'foo' => 'baz'];

        $cache->put($query, $params, $result);

        $this->assertFalse($cache->has($query, ['column_key' => 'valu2']));
    }

    /**
     *
     */
    public function testClear(): void
    {
        $cache  = new Cache;
        $query  = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';
        $params = ['column_key' => 'value'];
        $result = ['foo' => 'bar', 'foo' => 'baz'];

        $cache->put($query, $params, $result);

        $cache->clear('DELETE * FROM prefixed_my_table_name WHERE foo_column = :column_key');

        $this->assertFalse($cache->has($query, $params));
    }
}
