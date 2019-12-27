<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Media;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class MediaTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
    	$sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql, [], ['name' => 'foo']);

        $this->assertEquals('foo', $media->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
       	$sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql);

        $media->name = 'baz';

        $this->assertEquals('baz', $media->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql);

        $media->name = 'baz';

        $this->assertTrue(isset($media->name));

        $this->assertFalse(isset($media->email));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql);

        $media->name = 'baz';

        unset($media->name);

        $this->assertEquals(null, $media->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql, [], ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $media->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

		$media = new Media($sql, [], ['name' => 'foo']);

        $this->assertFalse($media->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $media = new Media($sql, [], ['id' => 2, 'path' => '/foo/bar/foo.jpg']);

        $sql->shouldReceive('DELETE_FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('UPDATE')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['thumbnail_id' => null])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('thumbnail_id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->twice()->andReturn(true);

        $this->assertTrue($media->delete());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $media = new Media($sql, [], ['id' => 2, 'path' => '/foo/bar/foo.jpg']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('path', '=', '/foo/bar/foo.jpg')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn([]);

        $sql->shouldReceive('INSERT_INTO')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['id' => 2, 'path' => '/foo/bar/foo.jpg'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($media->save());

        $this->assertEquals(4, $media->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $media = new Media($sql, [], ['id' => 2, 'path' => '/foo/bar/foo.jpg']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('path', '=', '/foo/bar/foo.jpg')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn(['id' => 2, 'path' => '/foo/bar/foo.jpg']);

        $sql->shouldReceive('UPDATE')->with('media_uploads')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['id' => 2, 'path' => '/foo/bar/foo.jpg'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($media->save());
    }

    /**
     *
     */
    public function testIsImage(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $media = new Media($sql, [], ['id' => 2, 'url' => '/foo/bar/foo.jpg']);

        $this->assertTrue($media->isImage());

        $media->url = 'foo/bar.png';

        $this->assertTrue($media->isImage());

        $media->url = 'foo/bar.gif';

        $this->assertTrue($media->isImage());
    }

    /**
     *
     */
    public function testImgSize(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $media = new Media($sql, [], ['id' => 2, 'url' => '/foo/bar/foo.jpg']);

        $this->assertEquals('/foo/bar/foo_small.jpg', $media->imgSize('small'));
    }
}
