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
    public function testInstantiateExisting()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());
    }

    /**
     *
     */
    public function testGetCategories()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->assertEquals(2, count($post->categories));

        $this->assertEquals('html', $post->category->name);

        $this->assertEquals('html', $post->categories[0]->name);
    }

    /**
     *
     */
    public function testGetTags()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->assertEquals(2, count($post->tags));

        $this->assertEquals('html', $post->tag->name);

        $this->assertEquals('html', $post->tags[0]->name);
    }

    /**
     *
     */
    public function testGetAuthor()
    {
        $sql              = Mockery::mock('\kanso\framework\database\query\Builder');
        $config           = Mockery::mock('\kanso\framework\config\Config');
        $tagProvider      = Mockery::mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = Mockery::mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = Mockery::mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = Mockery::mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = Mockery::mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->assertEquals('foobar', $post->author->name);
    }

    /**
     *
     */
    private function getExistingPostData()
    {
        return
        [
            'id'          => 1,
            'created'     => time(),
            'modified'    => time(),
            'status'      => 'published',
            'type'        => 'post',
            'slug'        => '/2018/01/foo-bar',
            'title'       => 'Foo Bar',
            'excerpt'     => 'Hello foo bar',
            'author_id'   => 1,
            'thumbnail_id'     => 1,
            'comments_enabled' => 1,
        ];
    }

    /**
     *
     */
    private function getTheTags($sql, $tagProvider)
    {
        $sql->shouldReceive('SELECT')->with('tags.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->with('tags', 'tags.id = tags_to_posts.tag_id')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([
            ['id' => 1, 'slug' => 'html', 'name' => 'html'],
            ['id' => 2, 'slug' => 'css',  'name' => 'css']
        ])->once();

        $tag1 = Mockery::mock('\kanso\cms\wrappers\Tag');
        $tag1->name = 'html';
        $tag1->slug = 'html';
        $tag1->id   = 1;

        $tag2 = Mockery::mock('\kanso\cms\wrappers\Tag');
        $tag2->name = 'css';
        $tag2->slug = 'css';
        $tag1->id   = 2;

        $tagProvider->shouldReceive('byId')->andReturn($tag1)->once();

        $tagProvider->shouldReceive('byId')->andReturn($tag2)->once();

        return [$tag1, $tag2];
    }

    /**
     *
     */
    private function getTheCategories($sql, $categoryProvider)
    {
        $sql->shouldReceive('SELECT')->with('categories.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('categories_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->with('categories', 'categories.id = categories_to_posts.category_id')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([
            ['id' => 1, 'slug' => 'html', 'name' => 'html'],
            ['id' => 2, 'slug' => 'css',  'name' => 'css']
        ])->once();

        $cat1 = Mockery::mock('\kanso\cms\wrappers\Category');
        $cat1->name = 'html';
        $cat1->slug = 'html';
        $cat1->id   = 1;

        $cat2 = Mockery::mock('\kanso\cms\wrappers\Category');
        $cat2->name = 'css';
        $cat2->slug = 'css';
        $cat2->id   = 2;

        $categoryProvider->shouldReceive('byId')->andReturn($cat1)->once();

        $categoryProvider->shouldReceive('byId')->andReturn($cat2)->once();

        return [$cat1, $cat2];
    }

    /**
     *
     */
    private function getTheAuthor($userProvider)
    {
        $author = Mockery::mock('\kanso\cms\wrappers\User');
        $author->name = 'foobar';
        $author->slug = 'foobar';
        $author->id = 1;

        $userProvider->shouldReceive('byId')->andReturn($author)->once();

        return $author;
    }
}
