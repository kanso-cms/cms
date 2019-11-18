<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers\providers;

use kanso\cms\wrappers\providers\TagProvider;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class TagProviderTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');
        $sql      = Mockery::mock('\kanso\framework\database\query\Builder');
        $provider = new TagProvider($sql);

        $sql->shouldReceive('INSERT_INTO')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['name' => 'foo', 'slug' => 'bar'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $tag = $provider->create(['name' => 'foo', 'slug' => 'bar']);

        $this->assertEquals(4, $tag->id);
    }

    /**
     *
     */
    public function testById(): void
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $provider = new TagProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byId(32);
    }

    /**
     *
     */
    public function testByKey(): void
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $provider = new TagProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $this->assertEquals('foo', $provider->byKey('name', 'foo', true)->name);
    }

    /**
     *
     */
    public function testByKeys(): void
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $provider = new TagProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 32, 'name' => 'foo', 'slug' => 'bar']]);

        $this->assertEquals('foo', $provider->byKey('name', 'foo')[0]->name);
    }
}
