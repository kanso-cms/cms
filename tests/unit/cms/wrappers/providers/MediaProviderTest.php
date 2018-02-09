<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\wrappers\providers;

use Mockery;
use tests\TestCase;
use kanso\cms\wrappers\providers\MediaProvider;

/**
 * @group unit
 */
class MediaProviderTest extends TestCase
{
    /**
     *
     */
    public function testCreate()
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');
        $sql      = Mockery::mock('\kanso\framework\database\query\Builder');
        $provider = new MediaProvider($sql, []);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('path', '=', '/foo/bar/foo.jpg')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn([]);

        $sql->shouldReceive('INSERT_INTO')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['path' => '/foo/bar/foo.jpg'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $media = $provider->create(['path' => '/foo/bar/foo.jpg']);

        $this->assertEquals(4, $media->id);
    }

    /**
     *
     */
    public function testById()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
        
        $provider = new MediaProvider($sql, []);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byId(32);
    }

    /**
     *
     */
    public function testByKey()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
        
        $provider = new MediaProvider($sql, []);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $this->assertEquals('foo', $provider->byKey('name', 'foo', true)->name);
    }

    /**
     *
     */
    public function testByKeys()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
        
        $provider = new MediaProvider($sql, []);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 32, 'name' => 'foo', 'slug' => 'bar']]);

        $this->assertEquals('foo', $provider->byKey('name', 'foo')[0]->name);
    }
}
