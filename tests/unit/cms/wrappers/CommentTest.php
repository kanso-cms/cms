<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Comment;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CommentTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
    	$sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertEquals('foo', $comment->post_id);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
       	$sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        $this->assertEquals('baz', $comment->post_id);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        $this->assertTrue(isset($comment->post_id));

        $this->assertFalse(isset($comment->email));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        unset($comment->post_id);

        $this->assertEquals(null, $comment->post_id);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertEquals(['post_id' => 'foo'], $comment->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertFalse($comment->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['id' => 2, 'post_id' => 'foo']);

		$sql->shouldReceive('DELETE_FROM')->with('comments')->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

		$sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->children($sql);

        $this->assertTrue($comment->delete());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
    	$cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['post_id' => 'foobar']);

		$sql->shouldReceive('INSERT_INTO')->with('comments')->once()->andReturn($sql);

		$sql->shouldReceive('VALUES')->with(['post_id' => 'foobar'])->once()->andReturn($sql);

		$sql->shouldReceive('QUERY')->once()->andReturn(true);

		$sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

		$cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($comment->save());

        $this->assertEquals(4, $comment->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
    	$cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$comment = new Comment($sql, ['id' => 3, 'post_id' => 'foobar']);

		$sql->shouldReceive('UPDATE')->with('comments')->once()->andReturn($sql);

		$sql->shouldReceive('SET')->with(['id' => 3, 'post_id' => 'foobar'])->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 3)->once()->andReturn($sql);

		$sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($comment->save());
    }

    /**
     *
     */
    public function testChildren(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $comment = new Comment($sql, ['id' => 3, 'post_id' => 'foobar', 'parent' => null]);

        $sql->shouldReceive('SELECT')->with('*')->times(3)->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->times(3)->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent', '=', 3)->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent', '=', 5)->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent', '=', 6)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([['id' => 5, 'slug' => 'categorieslug', 'parent' => null], ['id' => 6, 'slug' => 'categorieslug', 'parent' => null]])->once();

        $sql->shouldReceive('FIND_ALL')->andReturn([])->twice();

        $this->assertEquals(2, count($comment->children()));
    }

    /**
     *
     */
    private function children($sql): void
    {
       $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([]);
    }
}
