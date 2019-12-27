<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Category;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CategoryTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $category->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql);

        $category->name = 'baz';

        $this->assertEquals('baz', $category->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql);

        $category->name = 'baz';

        $this->assertTrue(isset($category->name));

        $this->assertFalse(isset($category->slug));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql);

        $category->name = 'baz';

        unset($category->name);

        $this->assertEquals(null, $category->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $category->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['name' => 'foo']);

        $this->assertFalse($category->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['id' => 2, 'name' => 'foo', 'parent_id' => null]);

        $sql->shouldReceive('DELETE_FROM')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->removeAllJoins($sql);

        $this->children($sql);

        $this->assertTrue($category->delete());
    }

    /**
     *
     */
    public function testDeleteFalse(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['name' => 'foo']);

        $this->assertFalse($category->delete());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteAdmin(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['id' => 1, 'name' => 'foo']);

        $category->delete();
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['slug' => 'categorieslug']);

        $sql->shouldReceive('INSERT_INTO')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['slug' => 'categorieslug'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($category->save());

        $this->assertEquals(4, $category->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['id' => 1, 'slug' => 'categorieslug']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('slug', '=', 'categorieslug')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn([]);

        $sql->shouldReceive('UPDATE')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['id' => 1, 'slug' => 'categorieslug'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($category->save());
    }

    /**
     *
     */
    public function testClear(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['id' => 2, 'slug' => 'categorieslug']);

        $this->removeAllJoins($sql);

        $this->children($sql);

        $this->assertTrue($category->clear());
    }

    /**
     *
     */
    public function testChildren(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $category = new Category($sql, ['id' => 2, 'slug' => 'categorieslug']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 5, 'slug' => 'categorieslug', 'parent_id' => null], ['id' => 6, 'slug' => 'categorieslug', 'parent_id' => null]]);

        $this->assertEquals(2, count($category->children()));
    }

    /**
     *
     */
    private function removeAllJoins($sql): void
    {
        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('categories_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->with('posts', 'categories_to_posts.post_id = posts.id')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('categories_to_posts.category_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([]);

        $sql->shouldReceive('DELETE_FROM')->with('categories_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('category_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);
    }

    /**
     *
     */
    private function children($sql): void
    {
        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('categories')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('parent_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([]);
    }
}
