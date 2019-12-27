<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers\providers;

use kanso\cms\wrappers\providers\PostProvider;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class PostProviderTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $cHandler         = $this->mock('\kanso\framework\database\connection\ConnectionHandler');
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $provider = new PostProvider($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $config->shouldReceive('get')->with('cms.permalinks')->andReturn('year/month/postname/');

        $tag = $this->mock('\kanso\cms\wrappers\Tag');
        $tag->name = 'html';
        $tag->slug = 'html';
        $tag->id   = 1;
        $tagProvider->shouldReceive('byId')->with(1)->once()->andReturn($tag);

        $cat = $this->mock('\kanso\cms\wrappers\Category');
        $cat->name = 'html';
        $cat->slug = 'html';
        $cat->id   = 1;
        $categoryProvider->shouldReceive('byId')->with(1)->once()->andReturn($cat);

        $user = $this->mock('\kanso\cms\wrappers\User');
        $user->name = 'foo';
        $user->slug = 'foo';
        $user->id   = 1;
        $userProvider->shouldReceive('byId')->with(1)->once()->andReturn($user);

        $postData = $this->getExistingPostData();

        unset($postData['id']);

        $sql->shouldReceive('SELECT')->with('id')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('title', '=', 'Foo Bar')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn([])->once();

        $sql->shouldReceive('INSERT_INTO')->with('posts')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with($postData)->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);
        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(1);

        $sql->shouldReceive('INSERT_INTO')->with('tags_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'tag_id' => 1])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $sql->shouldReceive('INSERT_INTO')->with('categories_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'category_id' => 1])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $sql->shouldReceive('INSERT_INTO')->with('content_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'content' => ''])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $data = $this->getExistingPostData();
        unset($data['id']);

        $post = $provider->create($data);

        $this->assertEquals(1, $post->id);
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
            'slug'        =>  date('Y') . '/' . date('m') . '/foo-bar/',
            'title'       => 'Foo Bar',
            'excerpt'     => 'Hello foo bar',
            'author_id'   => 1,
            'thumbnail_id'     => 1,
            'comments_enabled' => true,
        ];
    }

    /**
     *
     */
    public function testById(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $provider = new PostProvider($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('posts.id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->times(6)->andReturn($sql);

        $sql->shouldReceive('GROUP_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar', 'author_id' => 1]);

        $this->getTheTags($sql, $tagProvider);

        $this->getTheCategories($sql, $categoryProvider);

        $this->getTheAuthor($userProvider);

        $provider->byId(32);
    }

    /**
     *
     */
    public function testByKey(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $provider = new PostProvider($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->times(6)->andReturn($sql);

        $sql->shouldReceive('GROUP_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar', 'author_id' => 1]);

        $this->getTheTags($sql, $tagProvider);

        $this->getTheCategories($sql, $categoryProvider);

        $this->getTheAuthor($userProvider);

        $provider->byKey('name', 'foo', true, false);
    }

    /**
     *
     */
    public function testByKeys(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $provider = new PostProvider($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->times(6)->andReturn($sql);

        $sql->shouldReceive('GROUP_BY')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 32, 'name' => 'foo', 'slug' => 'bar', 'author_id' => 1]]);

        $this->getTheTags($sql, $tagProvider);

        $this->getTheCategories($sql, $categoryProvider);

        $this->getTheAuthor($userProvider);

        $provider->byKey('name', 'foo', false, false);
    }

    /**
     *
     */
    public function testPublished(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $provider = new PostProvider($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $sql->shouldReceive('SELECT')->with('posts.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('name', '=', 'foo')->once()->andReturn($sql);

        $sql->shouldReceive('AND_WHERE')->with('status', '=', 'published')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->times(6)->andReturn($sql);

        $sql->shouldReceive('GROUP_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 32, 'name' => 'foo', 'slug' => 'bar', 'author_id' => 1]);

        $this->getTheTags($sql, $tagProvider);

        $this->getTheCategories($sql, $categoryProvider);

        $this->getTheAuthor($userProvider);

        $provider->byKey('name', 'foo', true, true);
    }

    /**
     *
     */
    private function getTheTags($sql, $tagProvider)
    {
        $sql->shouldReceive('SELECT')->with('tags.*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('tags_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('LEFT_JOIN_ON')->with('tags', 'tags.id = tags_to_posts.tag_id')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([
            ['id' => 1, 'slug' => 'html', 'name' => 'html'],
            ['id' => 2, 'slug' => 'css',  'name' => 'css'],
        ])->once();

        $tag1 = $this->mock('\kanso\cms\wrappers\Tag');
        $tag1->name = 'html';
        $tag1->slug = 'html';
        $tag1->id   = 1;

        $tag2 = $this->mock('\kanso\cms\wrappers\Tag');
        $tag2->name = 'css';
        $tag2->slug = 'css';
        $tag2->id   = 2;

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

        $sql->shouldReceive('WHERE')->with('post_id', '=', 32)->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([
            ['id' => 1, 'slug' => 'html', 'name' => 'html'],
            ['id' => 2, 'slug' => 'css',  'name' => 'css'],
        ])->once();

        $cat1 = $this->mock('\kanso\cms\wrappers\Category');
        $cat1->name = 'html';
        $cat1->slug = 'html';
        $cat1->id   = 1;

        $cat2 = $this->mock('\kanso\cms\wrappers\Category');
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
        $author = $this->mock('\kanso\cms\wrappers\User');
        $author->name = 'foobar';
        $author->slug = 'foobar';
        $author->id = 1;

        $userProvider->shouldReceive('byId')->andReturn($author)->once();

        return $author;
    }

}
