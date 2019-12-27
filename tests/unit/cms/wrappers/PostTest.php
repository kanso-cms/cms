<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Post;
use kanso\framework\utility\Str;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class PostTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'foo';

        $this->assertEquals('foo', $post->title);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        $this->assertEquals('baz', $post->title);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        $this->assertTrue(isset($post->title));

        $this->assertFalse(isset($post->email));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        $post->title = 'baz';

        unset($post->title);

        $this->assertEquals(null, $post->title);
    }

    /**
     *
     */
    public function testInstantiateExisting(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());
    }

    /**
     *
     */
    public function testGetCategories(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

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
    public function testGetTags(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

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
    public function testGetAuthor(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->assertEquals('foobar', $post->author->name);
    }

    /**
     *
     */
    public function testGetContent(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->getTheContent($sql);

        $this->assertEquals('foobar', $post->content);
    }

    /**
     *
     */
    public function testGetComments(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $comments = $this->getTheComments($sql, $commentProvider);

        $this->assertEquals(2, count($post->comments));
    }

    /**
     *
     */
    public function testGetThumbnail(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $thumbnail = $this->getThumbnail($mediaProvider);

        $this->assertEquals(1, $post->thumbnail->id);
    }

    /**
     *
     */
    public function testGetExcerpt(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->assertEquals('Hello foo bar', $post->excerpt);
    }

    /**
     *
     */
    public function testGetMeta(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $this->getMeta($sql);

        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $post->meta);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $sql->shouldReceive('DELETE_FROM')->with('comments')->once()->andReturn($sql);
        $sql->shouldReceive('DELETE_FROM')->with('tags_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('DELETE_FROM')->with('categories_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('DELETE_FROM')->with('content_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('DELETE_FROM')->with('posts')->once()->andReturn($sql);
        $sql->shouldReceive('DELETE_FROM')->with('post_meta')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('id', '=', 1)->andReturn($sql);

        $sql->shouldReceive('QUERY')->times(6)->andReturn($sql);

        $this->assertTrue($post->delete());
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $tags = $this->getTheTags($sql, $tagProvider);

        $cats = $this->getTheCategories($sql, $categoryProvider);

        $author = $this->getTheAuthor($userProvider);

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider, $this->getExistingPostData());

        $content = $this->getTheContent($sql);

        $meta = $this->getMeta($sql);

        $config->shouldReceive('get')->with('cms.permalinks')->andReturn('year/month/postname/');

        $sql->shouldReceive('UPDATE')->with('posts')->once()->andReturn($sql);
        $sql->shouldReceive('SET')->with($this->getExistingPostData())->once()->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('tags_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('categories_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('content_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('post_meta')->once()->andReturn($sql);
        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->times(5);

        $sql->shouldReceive('INSERT_INTO')->with('tags_to_posts')->twice()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'tag_id' => 1])->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'tag_id' => 2])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->times(2);

        $sql->shouldReceive('INSERT_INTO')->with('categories_to_posts')->twice()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'category_id' => 1])->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'category_id' => 2])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->times(2);

        $sql->shouldReceive('INSERT_INTO')->with('content_to_posts')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'content' => 'foobar'])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $sql->shouldReceive('INSERT_INTO')->with('post_meta')->once()->andReturn($sql);
        $sql->shouldReceive('VALUES')->with(['post_id' => 1, 'content' => Str::mysqlEncode(serialize($meta))])->once()->andReturn($sql);
        $sql->shouldReceive('QUERY')->once();

        $tagProvider->shouldReceive('byId')->with(1)->once();

        $categoryProvider->shouldReceive('byId')->with(1)->once();

        $this->assertTrue($post->save());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler         = $this->mock('\kanso\framework\database\connection\ConnectionHandler');
        $sql              = $this->mock('\kanso\framework\database\query\Builder');
        $config           = $this->mock('\kanso\framework\config\Config');
        $tagProvider      = $this->mock('\kanso\cms\wrappers\providers\TagProvider');
        $categoryProvider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $mediaProvider    = $this->mock('\kanso\cms\wrappers\providers\MediaProvider');
        $commentProvider  = $this->mock('\kanso\cms\wrappers\providers\CommentProvider');
        $userProvider     = $this->mock('\kanso\cms\wrappers\providers\UserProvider');

        $post = new Post($sql, $config, $tagProvider, $categoryProvider, $mediaProvider, $commentProvider, $userProvider);

        foreach ($this->getExistingPostData() as $key => $value)
        {
            if ($key === 'id')
            {
                continue;
            }

            $post->{$key} = $value;
        }

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

        $this->assertTrue($post->save());
    }

    /**
     *
     */
    private function getExistingPostData()
    {
        return
        [
            'id'               => 1,
            'created'          => time(),
            'modified'         => time(),
            'status'           => 'published',
            'type'             => 'post',
            'slug'             =>  date('Y') . '/' . date('m') . '/foo-bar/',
            'title'            => 'Foo Bar',
            'excerpt'          => 'Hello foo bar',
            'author_id'        => 1,
            'thumbnail_id'     => 1,
            'comments_enabled' => true,
        ];
    }

    /**
     *
     */
    private function getTheContent($sql): void
    {
        $sql->shouldReceive('SELECT')->with('content')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('content_to_posts')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn(['content' => 'foobar'])->once();
    }

    /**
     *
     */
    private function getMeta($sql)
    {
        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('post_meta')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->andReturn(['id' => 1, 'post_id' => 1, 'content' => serialize(['foo' => 'bar', 'bar' => 'baz'])])->once();

        return ['foo' => 'bar', 'bar' => 'baz'];
    }

    /**
     *
     */
    private function getThumbnail($mediaProvider)
    {
        $thumbnail = $this->mock('\kanso\cms\wrappers\Media');

        $thumbnail->id = 1;

        $mediaProvider->shouldReceive('byId')->with(1)->once()->andReturn($thumbnail);

        return $thumbnail;
    }

    /**
     *
     */
    private function getTheComments($sql, $commentProvider): void
    {
        $sql->shouldReceive('SELECT')->with('id')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('comments')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('AND_WHERE')->with('parent', '=', 0)->once()->andReturn($sql);

        $sql->shouldReceive('AND_WHERE')->with('status', '=', 'approved')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([
            ['id' => 1],
            ['id' => 2],
        ])->once();

        $comment1 = $this->mock('\kanso\cms\wrappers\Comment');
        $comment1->id   = 1;

        $comment2 = $this->mock('\kanso\cms\wrappers\Comment');
        $comment2->id   = 2;

        $commentProvider->shouldReceive('byId')->andReturn($comment1)->once();

        $commentProvider->shouldReceive('byId')->andReturn($comment2)->once();
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

        $sql->shouldReceive('WHERE')->with('post_id', '=', 1)->once()->andReturn($sql);

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
