<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\providers;

use kanso\framework\database\query\Builder;
use kanso\framework\config\Config;
use kanso\cms\wrappers\Post;
use kanso\cms\wrappers\providers\Provider;
use kanso\cms\wrappers\providers\TagProvider;
use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\cms\wrappers\providers\MediaProvider;
use kanso\cms\wrappers\providers\CommentProvider;

/**
 * Post provider
 *
 * @author Joe J. Howard
 */
class PostProvider extends Provider
{
    private $tagProvider;

    private $categoryProvider;

    private $mediaProvider;

    private $userProvider;

    private $commentProvider;

    /**
     * Override inherited constructor
     * 
     * @access public
     */
    public function __construct(Builder $SQL, Config $config, TagProvider $tagProvider, CategoryProvider $categoryProvider, MediaProvider $mediaProvider, CommentProvider $commentProvider, UserProvider $userProvider)
    {
        $this->SQL = $SQL;

        $this->config = $config;

        $this->tagProvider = $tagProvider;

        $this->categoryProvider = $categoryProvider;

        $this->mediaProvider = $mediaProvider;

        $this->commentProvider = $commentProvider;

        $this->userProvider = $userProvider;
    }

    private function newPost(array $row)
    {
        return new Post($this->SQL, $this->config, $this->tagProvider, $this->categoryProvider, $this->mediaProvider, $this->commentProvider, $this->userProvider, $row);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $post = $this->newPost($row);

        if ($post->save())
        {
            return $post;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function byId(int $id)
    {
        return $this->byKey('id', $id, true, false);
    }

    /**
     * {@inheritdoc}
     */
    public function byKey(string $index, $value, bool $single = false, bool $published = true)
    {
        if ($index === 'id')
        {
            $index = 'posts.id';
        }

        $this->SQL->SELECT('posts.*')->FROM('posts')->WHERE($index, '=', $value)
        ->LEFT_JOIN_ON('users', 'users.id = posts.author_id')
        ->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id')
        ->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id')
        ->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id')
        ->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')
        ->GROUP_BY('posts.id');

        if ($published)
        {
            $this->SQL->AND_WHERE('status', '=', 'published');
        }

        if ($single)
        {
            $post = $this->SQL->ROW();
            
            if ($post)
            {
                return $this->newPost($post);
            }

            return null;
        }
        else
        {
            $posts = [];

            $rows = $this->SQL->FIND_ALL();

            foreach ($rows as $row)
            {
                $posts[] = $this->newPost($row);
            }

            return $posts;
        }
    }
}
