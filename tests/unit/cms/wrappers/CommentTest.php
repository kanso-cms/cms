<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\wrappers;

use Mockery;
use tests\TestCase;
use kanso\cms\wrappers\Comment;

/**
 * @group unit
 */
class CommentTest extends TestCase
{
	/**
     *
     */
    public function testInstantiate()
    {
    	$sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertEquals('foo', $comment->post_id);
    }

    /**
     *
     */
    public function testSetGet()
    {
       	$sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        $this->assertEquals('baz', $comment->post_id);
    }

    /**
     *
     */
    public function testHas()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        $this->assertTrue(isset($comment->post_id));

        $this->assertFalse(isset($comment->email));
    }

    /**
     *
     */
    public function testRemove()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql);

        $comment->post_id = 'baz';

        unset($comment->post_id);

        $this->assertEquals(null, $comment->post_id);
    }

    /**
     *
     */
    public function testAsArray()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertEquals(['post_id' => 'foo'], $comment->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql, ['post_id' => 'foo']);

        $this->assertFalse($comment->delete());
    }

    /**
     *
     */
    public function testDeleteTrue()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
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
    public function testSaveNew()
    {
    	$cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
		$comment = new Comment($sql, [ 'post_id' => 'foobar']);

		$sql->shouldReceive('INSERT_INTO')->with('comments')->once()->andReturn($sql);

		$sql->shouldReceive('VALUES')->with([ 'post_id' => 'foobar'])->once()->andReturn($sql);

		$sql->shouldReceive('QUERY')->once()->andReturn(true);

		$sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

		$cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($comment->save());

        $this->assertEquals(4, $comment->id);
    }

    /**
     *
     */
    public function testSaveExisting()
    {
    	$cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
		
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
    public function testChildren()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');
        
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
    private function children($sql)
    {
       $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([]);
    }
}
