<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\wrappers;

use Mockery;
use tests\TestCase;
use kanso\cms\wrappers\Post;

/**
 * @group unit
 */
class PostTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'foo';

        $this->assertEquals('foo', $post->title);
    }

    /**
     *
     */
    public function testSetGet()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        $this->assertEquals('baz', $post->title);
    }

    /**
     *
     */
    public function testHas()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        $this->assertTrue(isset($post->title));

        $this->assertFalse(isset($post->email));
    }

    /**
     *
     */
    public function testRemove()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        unset($post->title);

        $this->assertEquals(null, $post->title);
    }

    /**
     *
     */
    public function testDeleteEmpty()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $this->assertFalse($post->delete());
    }
}
