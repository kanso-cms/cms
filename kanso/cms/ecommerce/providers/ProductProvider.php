<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\providers;

use kanso\cms\wrappers\providers\Provider;
use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\cms\wrappers\providers\CommentProvider;
use kanso\cms\wrappers\providers\LeadProvider;
use kanso\cms\wrappers\providers\MediaProvider;
use kanso\cms\wrappers\providers\TagProvider;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\cms\wrappers\Post;
use kanso\framework\config\Config;
use kanso\framework\database\query\Builder;

/**
 * Product provider.
 *
 * @author Joe J. Howard
 */
class ProductProvider extends Provider
{
    /**
     * Config.
     *
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * Tag provider.
     *
     * @var \kanso\cms\wrappers\providers\TagProvider
     */
    private $tagProvider;

    /**
     * Category provider.
     *
     * @var \kanso\cms\wrappers\providers\CategoryProvider
     */
    private $categoryProvider;

    /**
     * Media provider.
     *
     * @var \kanso\cms\wrappers\providers\MediaProvider
     */
    private $mediaProvider;

    /**
     * User provider.
     *
     * @var \kanso\cms\wrappers\providers\UserProvider
     */
    private $userProvider;

    /**
     * Comment provider.
     *
     * @var \kanso\cms\wrappers\providers\CommentProvider
     */
    private $commentProvider;

    /**
     * Override inherited constructor.
     *
     * @param \kanso\framework\database\query\Builder        $SQL              SQL query builder
     * @param \kanso\framework\config\Config                 $config           Configuration instance
     * @param \kanso\cms\wrappers\providers\TagProvider      $tagProvider      Tag provider instance
     * @param \kanso\cms\wrappers\providers\CategoryProvider $categoryProvider Category provider instance
     * @param \kanso\cms\wrappers\providers\MediaProvider    $mediaProvider    Media provider instance
     * @param \kanso\cms\wrappers\providers\UserProvider     $userProvider     User provider instance
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

    /**
     * Create and return new post wrapper around a database entry.
     *
     * @param  array                    $row Row from the database
     * @return \kanso\cms\wrappers\Post
     */
    public function newProduct(array $row): Post
    {
        return new Post($this->SQL, $this->config, $this->tagProvider, $this->categoryProvider, $this->mediaProvider, $this->commentProvider, $this->userProvider, $row);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $post = $this->newProduct($row);

        if ($post->save())
        {
            return $post;
        }

        return false;
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
        ->LEFT_JOIN_ON('categories_to_posts', 'posts.id = categories_to_posts.post_id')
        ->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id')
        ->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id')
        ->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')
        ->GROUP_BY('posts.id');

        $this->SQL->AND_WHERE('type', '=', 'product');

        if ($published)
        {
            $this->SQL->AND_WHERE('status', '=', 'published');
        }

        if ($single)
        {
            $post = $this->SQL->ROW();

            if ($post)
            {
                return $this->newProduct($post);
            }

            return null;
        }
        else
        {
            $posts = [];

            $rows = $this->SQL->FIND_ALL();

            foreach ($rows as $row)
            {
                $posts[] = $this->newProduct($row);
            }

            return $posts;
        }
    }

    /**
     * Get all of a products SKUs by product id
     * 
     * @param  int $productId Product id
     * @return array
     */
    public function skus(int $productId): array
    {
        $product = $this->byId($productId);

        if ($product)
        {
            return $product->meta['skus'];
        }

        return [];
    }

    /**
     * Get a product's sku by SKU or the first SKU
     * 
     * @param int    $productId Product id
     * @param string $sku       Product sku (optional default '')
     * @return array
     */
    public function sku(int $productId, string $sku = ''): array
    {
        $skus = $this->skus($productId);

        if ($sku === '' && isset($skus[0]))
        {
            return $skus[0];
        }

        foreach ($skus as $_sku)
        {
            if ($_sku['sku'] === $sku)
            {
                return $_sku;
            }
        }

        return [];
    }

    /**
     * Get all products
     * 
     * @param bool $published Return only published products (optional) (default true)
     */
    public function all(bool $published = true): array
    {
        $posts = [];

        $this->SQL->SELECT('posts.*')->FROM('posts')->WHERE('type', '=', 'product')
        ->LEFT_JOIN_ON('users', 'users.id = posts.author_id')
        ->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id')
        ->LEFT_JOIN_ON('categories_to_posts', 'posts.id = categories_to_posts.post_id')
        ->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id')
        ->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id')
        ->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')
        ->GROUP_BY('posts.id');

        if ($published)
        {
            $this->SQL->AND_WHERE('status', '=', 'published');
        }
        
        $rows = $this->SQL->FIND_ALL();

        foreach ($rows as $row)
        {
            $posts[] = $this->newProduct($row);
        }

        return $posts;
    }
}