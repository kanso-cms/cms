<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers\providers;

use kanso\cms\wrappers\providers\CommentProvider;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CommentProviderTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = new CommentProvider($sql);

        $sql->shouldReceive('INSERT_INTO')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['post_id' => 1])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $comment = $provider->create(['post_id' => 1]);

        $this->assertEquals(4, $comment->id);
    }

    /**
     *
     */
    public function testById(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $provider = new CommentProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $provider->byId(32);
    }

    /**
     *
     */
    public function testByKey(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $provider = new CommentProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar']);

        $this->assertEquals('foo', $provider->byKey('name', 'foo', true)->name);
    }

    /**
     *
     */
    public function testByKeys(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $provider = new CommentProvider($sql);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 32, 'name' => 'foo', 'slug' => 'bar']]);

        $this->assertEquals('foo', $provider->byKey('name', 'foo')[0]->name);
    }
}
