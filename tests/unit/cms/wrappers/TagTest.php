<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Tag;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class TagTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $tag->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql);

        $tag->name = 'baz';

        $this->assertEquals('baz', $tag->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql);

        $tag->name = 'baz';

        $this->assertTrue(isset($tag->name));

        $this->assertFalse(isset($tag->slug));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql);

        $tag->name = 'baz';

        unset($tag->name);

        $this->assertEquals(null, $tag->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $tag->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['name' => 'foo']);

        $this->assertFalse($tag->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['id' => 2, 'name' => 'foo']);

        $sql->shouldReceive('DELETE_FROM')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->removeAllJoins($sql);

        $this->assertTrue($tag->delete());
    }

    /**
     *
     */
    public function testDeleteFalse(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['name' => 'foo']);

        $this->assertFalse($tag->delete());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteAdmin(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['id' => 1, 'name' => 'foo']);

        $tag->delete();
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['slug' => 'tagslug']);

        $sql->shouldReceive('INSERT_INTO')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['slug' => 'tagslug'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($tag->save());

        $this->assertEquals(4, $tag->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['id' => 1, 'slug' => 'tagslug']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('slug', '=', 'tagslug')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn([]);

        $sql->shouldReceive('UPDATE')->with('tags')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['id' => 1, 'slug' => 'tagslug'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($tag->save());
    }

    /**
     *
     */
    public function testClear(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $tag = new Tag($sql, ['id' => 2, 'slug' => 'tagslug']);

        $this->removeAllJoins($sql);

        $this->assertTrue($tag->clear());
    }

    /**
     *
     */
    private function removeAllJoins($sql): void
    {
        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->with('posts', 'tags_to_posts.post_id = posts.id')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('tags_to_posts.tag_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([]);

        $sql->shouldReceive('DELETE_FROM')->with('tags_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('tag_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);
    }
}
