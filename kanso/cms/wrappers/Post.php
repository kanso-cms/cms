<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\cms\wrappers\providers\CommentProvider;
use kanso\cms\wrappers\providers\MediaProvider;
use kanso\cms\wrappers\providers\TagProvider;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\framework\config\Config;
use kanso\framework\database\query\Builder;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Markdown;
use kanso\framework\utility\Str;

/**
 * Category utility wrapper.
 *
 * @author Joe J. Howard
 */
class Post extends Wrapper
{
	/**
	 * Pending changes that need to be saved.
	 *
	 * @var array
	 */
	private $pending = [];

	/**
	 * Assoc array of post data.
	 *
	 * @var array
	 */
	private $defaults =
	[
		'created'     => null,
		'modified'    => null,
		'status'      => 'draft',
		'type'        => '',
		'slug'        => '',
		'title'       => '',
		'excerpt'     => '',
		'author_id'   => 1,
		'thumbnail_id'     => null,
		'comments_enabled' => false,

		// Joins
		'tags' 	      => null,
		'categories'  => null,
		'author'      => null,
		'comments'    => null,
		'thumbnail'   => null,
		'content'     => null,
		'meta'        => null,
	];

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
     * Framework configuration.
     *
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * Override inherited constructor.
     *
     * @access public
     */
    public function __construct(Builder $SQL, Config $config, TagProvider $tagProvider, CategoryProvider $categoryProvider, MediaProvider $mediaProvider, CommentProvider $commentProvider, UserProvider $userProvider, array $data = [])
    {
        $this->SQL = $SQL;

        $this->config = $config;

        $this->tagProvider = $tagProvider;

        $this->categoryProvider = $categoryProvider;

        $this->mediaProvider = $mediaProvider;

        $this->commentProvider = $commentProvider;

        $this->userProvider = $userProvider;

        foreach ($this->defaults as $column => $default)
        {
        	if (!array_key_exists($column, $data))
        	{
        		$data[$column] = $default;
        	}
        }

        $this->data = $data;

        if (!empty($this->data['id']))
		{
			$this->getTheTags();

			$this->getTheAuthor();

			$this->getTheCategories();
		}

        if (!isset($this->data['created']))
        {
        	$this->data['created'] = time();
        }
    }

	/**
	 * {@inheritdoc}
	 */
	public function __get(string $key)
	{
		if ($key === 'categories')
		{
			return $this->getTheCategories();
		}
		elseif ($key === 'category')
		{
			return $this->getTheCategories()[0];
		}
		elseif ($key === 'tags')
		{
			return $this->getTheTags();
		}
		elseif ($key === 'tag')
		{
			return $this->getTheTags()[0];
		}
		elseif ($key === 'author')
		{
			return $this->getTheAuthor();
		}
		elseif ($key === 'content')
		{
			return $this->getTheContent();
		}
		elseif ($key === 'comments')
		{
			return $this->getTheComments();
		}
		elseif ($key === 'excerpt')
		{
			return $this->getTheExceprt();
		}
		elseif ($key === 'meta')
		{
			return $this->getPostMeta();
		}
		elseif ($key === 'thumbnail')
		{
			return $this->getTheThumbnail();
		}
		elseif (array_key_exists($key, $this->data))
		{
			return $this->data[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Set a value by key.
	 *
	 * @access public
	 * @param string $key   Key to save value to
	 * @param mixed  $value Value to save
	 */
	public function __set(string $key, $value)
	{
		if ($key === 'tags')
		{
			$this->setPending('tags', $value);
		}
		elseif ($key === 'categories')
		{
			$this->setPending('categories', $value);
		}
		elseif ($key === 'author')
		{
			$this->setPending('author_id', $value);
		}
		elseif ($key === 'excerpt')
		{
			$this->data['excerpt'] = Str::reduce(Markdown::plainText($value), 350);
		}
		elseif ($key === 'content')
		{
			$this->data['content'] = $value;
		}
		elseif (array_key_exists($key, $this->data))
		{
			$this->data[$key] = $value;
		}
	}

	/**
	 * Set a pending key/value on a join table that needs to be saved.
	 *
	 * @access private
	 * @param string $key   Key to set
	 * @param mixed  $value Value to save
	 */
	private function setPending(string $key, $value)
	{
		$this->pending[$key] = $value;
	}

	/**
	 *  Get the array of category objects.
	 *
	 * @access private
	 * @return array
	 */
	private function getTheCategories(): array
	{
		if (!empty($this->data['id']))
		{
			if (is_null($this->data['categories']))
			{
				$this->data['categories'] = [];

				$cats = $this->SQL->SELECT('categories.*')->FROM('categories_to_posts')->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id')->WHERE('post_id', '=', $this->data['id'])->FIND_ALL();

				foreach ($cats as $cat)
				{
					$this->data['categories'][] = $this->categoryProvider->byId($cat['id']);
				}
			}
		}

		if (empty($this->data['categories']))
		{
			$this->data['categories'] = [$this->categoryProvider->byId(1)];
		}

		return $this->data['categories'];
	}

	/**
	 * Get the array of tag objects.
	 *
	 * @access private
	 * @return array
	 */
	private function getTheTags(): array
	{
		if (!empty($this->data['id']))
		{
			if (is_null($this->data['tags']))
			{
				$this->data['tags'] = [];

				$tags = $this->SQL->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('post_id', '=', $this->data['id'])->FIND_ALL();

				foreach ($tags as $tag)
				{
					$this->data['tags'][] = $this->tagProvider->byId($tag['id']);
				}
			}
		}

		if (empty($this->data['tags']))
		{
			$this->data['tags'] = [$this->tagProvider->byId(1)];
		}

		return $this->data['tags'];
	}

	/**
	 * Get the post content.
	 *
	 * @access private
	 * @return string
	 */
	private function getTheContent(): string
	{
		if (!empty($this->data['id']))
		{
			if (is_null($this->data['content']))
			{
				$content = $this->SQL->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', $this->data['id'])->ROW();

				if (isset($content['content']))
				{
					$this->data['content'] = $content['content'];
				}
				else
				{
					$this->data['content'] = '';
				}
			}
		}

		if (!isset($this->data['content']) || $this->data['content'] === null)
		{
			$this->data['content'] = '';
		}

		return Str::mysqlDecode($this->data['content']);
	}

	/**
	 * Get the post excerpt.
	 *
	 * @access private
	 * @return string
	 */
	private function getTheExceprt(): string
	{
		return !empty($this->data['excerpt']) ? Str::mysqlDecode($this->data['excerpt']) : '';
	}

	/**
	 * Get the post author.
	 *
	 * @access private
	 * @return \kanso\cms\wrappers\User|null
	 */
	private function getTheAuthor()
	{
		if (!empty($this->data['author_id']))
		{
			if (is_null($this->data['author']))
			{
				$this->data['author'] = $this->userProvider->byId($this->data['author_id']);
			}
			elseif ($this->data['author'] && $this->data['author']->id !== $this->data['author_id'])
			{
				$this->data['author'] = $this->userProvider->byId($this->data['author_id']);
			}
		}

		// Fallback
		if (empty($this->data['author']) || empty($this->data['author_id']))
		{
			$this->data['author'] = $this->userProvider->byId(1);

			$this->data['author_id'] = 1;
		}

		return $this->data['author'];
	}

	/**
	 * Get the post comments.
	 *
	 * @access private
	 * @return array
	 */
	private function getTheComments(): array
	{
		if (!empty($this->data['id']))
		{
			if (is_null($this->data['comments']))
			{
				$this->data['comments'] = [];

				$comments = $this->SQL->SELECT('id')->FROM('comments')->WHERE('post_id', '=', $this->data['id'])->AND_WHERE('parent', '=', 0)->AND_WHERE('status', '=', 'approved')->FIND_ALL();

				foreach ($comments as $comment)
				{
					$this->data['comments'][] = $this->commentProvider->byId($comment['id']);
				}
			}
		}
		else
		{
			$this->data['comments'] = [];
		}

		return $this->data['comments'];
	}

	/**
	 * Get the post thumbnail.
	 *
	 * @access private
	 * @return mixed
	 */
	private function getTheThumbnail()
	{
		if (!empty($this->data['thumbnail_id']))
		{
			if (is_null($this->data['thumbnail']))
			{
				$this->data['thumbnail'] = $this->mediaProvider->byId($this->data['thumbnail_id']);
			}
		}

		return $this->data['thumbnail'];
	}

	/**
	 * Get the post meta.
	 *
	 * @access private
	 * @return array
	 */
	private function getPostMeta(): array
	{
		if (!empty($this->data['meta']))
		{
			return $this->data['meta'];
		}

		if (!empty($this->data['id']))
		{
			$meta = $this->SQL->SELECT('*')->FROM('post_meta')->WHERE('post_id', '=', $this->data['id'])->ROW();

			if ($meta)
			{
				$this->data['meta'] = unserialize(Str::mysqlDecode($meta['content']));
			}
			else
			{
				$this->data['meta'] = [];
			}

			return $this->data['meta'];
		}

		$this->data['meta'] = [];

		return $this->data['meta'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(): bool
	{
		$row = $this->data;

		foreach ($this->pending as $key => $value)
		{
			$row[$key] = $value;
		}

		$newPost = isset($row['id']) && $row['id'] > 0 ? false : true;

		$row['modified'] = time();

		// If the tags don't exist - create them
		$row['tags'] = $this->createTags($row['tags']);

		// If the tags don't exist - create them
		$row['categories'] = $this->createCategories($row['categories']);

		// Make sure there is a valid author
		$row['author']    = $this->getTheAuthor();
		$row['author_id'] = $row['author']->id;

		// Get the content
		$row['content'] = Str::mysqlEncode($this->getTheContent());

		$row['excerpt'] = Str::mysqlEncode($this->getTheExceprt());

		// Validate the title
		$row['title'] = trim($row['title']);
		if (empty($row['title']))
		{
			$row['title'] = 'Untitled';
		}

		if ($newPost || $row['title'] === 'Untitled')
		{
			$row['title'] = $this->uniqueBaseTitle($row['title']);
		}

		// Sanitize the thumbnail
		$row['thumbnail_id'] = intval($row['thumbnail_id']);
		if ($row['thumbnail_id'] === 0)
		{
			$row['thumbnail_id'] = null;
		}

		// Slug may or may not have been set manually
		$row['slug'] = isset($row['slug']) ? $row['slug'] : false;

		// Create a slug based on the category, tags, slug, author
		$row['slug'] = trim(preg_replace('/-+/', '-', $this->titleToSlug($row['title'], $row['categories'][0]->slug, $row['author']->slug, $row['created'], $row['type'], $row['slug'])), '-');

		// Sanitize comments_enabled
		$row['comments_enabled'] = boolval($row['comments_enabled']);

		// Get the post meta
		$postMeta = empty($row['meta']) ? $this->getPostMeta() : $row['meta'];

		// Remove joined rows so we can update/insert
		$insertRow = Arr::unsets(['thumbnail', 'tags', 'categories', 'content', 'comments', 'author', 'meta'], $row);

		// Insert a new article
		if ($newPost)
		{
			unset($insertRow['id']);

			$this->SQL->INSERT_INTO('posts')->VALUES($insertRow)->QUERY();

			$row['id'] = intval($this->SQL->connectionHandler()->lastInsertId());
		}

		// Or update an existing row
		else
		{
			$this->SQL->UPDATE('posts')->SET($insertRow)->WHERE('id', '=', $row['id'])->QUERY();

			$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();

			$this->SQL->DELETE_FROM('categories_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();

			$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();

			$this->SQL->DELETE_FROM('post_meta')->WHERE('post_id', '=', $row['id'])->QUERY();
		}

		// Join the tags
		foreach ($row['tags'] as $tag)
		{
			$this->SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $row['id'], 'tag_id' => $tag->id])->QUERY();
		}

		// Join the categories
		foreach ($row['categories'] as $cat)
		{
			$this->SQL->INSERT_INTO('categories_to_posts')->VALUES(['post_id' => $row['id'], 'category_id' => $cat->id])->QUERY();
		}

		// Join the content
		$this->SQL->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $row['id'], 'content' => $row['content']])->QUERY();

		// Join the post meta
		if (!empty($postMeta))
		{
			$this->SQL->INSERT_INTO('post_meta')->VALUES(['post_id' => $row['id'], 'content' => Str::mysqlEncode(serialize($postMeta))])->QUERY();
		}

		$this->data = $row;

		$this->pending = [];

		// return the row data
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(): bool
	{
		if (isset($this->data['id']))
		{
			$this->SQL->DELETE_FROM('comments')->WHERE('post_id', '=', $this->data['id'])->QUERY();

			$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $this->data['id'])->QUERY();

			$this->SQL->DELETE_FROM('categories_to_posts')->WHERE('post_id', '=', $this->data['id'])->QUERY();

			$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $this->data['id'])->QUERY();

			$this->SQL->DELETE_FROM('posts')->WHERE('id', '=', $this->data['id'])->QUERY();

			$this->SQL->DELETE_FROM('post_meta')->WHERE('post_id', '=', $this->data['id'])->QUERY();

			return true;
		}

		return false;
	}

	/**
	 * Creates categories if they don't already exist.
	 *
	 * @access private
	 * @param  mixed $cats categories names, or category wrapper
	 * @return array
	 */
	private function createCategories($cats): array
	{
		$default = [$this->categoryProvider->byId(1)];
		$result  = [];

	  	if (is_string($cats))
	   	{
	   		$cats = array_filter(array_map('trim', explode(',', $cats)));
	   	}

	   	if (empty($cats) || !is_array($cats))
		{
			return $default;
		}

	  	foreach ($cats as $cat)
	  	{
	  		if ($cat instanceof Category)
	        {
	        	$result[] = $cat;
	        }
	        elseif (is_string($cat))
		   	{
		   		if (ucfirst($cat) === 'Uncategorized')
		   		{
		   			continue;
		   		}

		   		$catWrapper = $this->categoryProvider->byKey('name', $cat, true);

		   		if ($catWrapper)
		   		{
		   			$result[] = $catWrapper;
		   		}
		   		else
		   		{
		   			$result[] = $this->categoryProvider->create([
				  		'name' => $cat,
				  		'slug' => Str::slug($cat),
				  	]);
		   		}
		   	}
	  	}

	  	if (empty($result))
	  	{
	  		return $default;
	  	}

	  	return $result;
	}

	/**
	 * Creates tags if they don't already exist.
	 *
	 * @access private
	 * @param  mixed $tags tag names, array of Tags, or array of tag names
	 * @return array
	 */
	private function createTags($tags): array
	{
		$default = [$this->tagProvider->byId(1)];

		$result  = [];

	  	if (is_string($tags))
	   	{
	   		$tags = array_filter(array_map('trim', explode(',', $tags)));
	   	}

	   	if (empty($tags) || !is_array($tags))
		{
			return $default;
		}

	  	foreach ($tags as $tag)
	  	{
	  		if ($tag instanceof Tag)
	        {
	        	$result[] = $tag;
	        }
	        elseif (is_string($tag))
		   	{
		   		if (ucfirst($tag) === 'Untagged')
		   		{
		   			continue;
		   		}

		   		$tagWrapper = $this->tagProvider->byKey('name', $tag, true);

		   		if ($tagWrapper)
		   		{
		   			$result[] = $tagWrapper;
		   		}
		   		else
		   		{
		   			$result[] = $this->tagProvider->create([
				  		'name' => $tag,
				  		'slug' => Str::slug($tag),
				  	]);
		   		}
		   	}
	  	}

	  	if (empty($result))
	  	{
	  		return $default;
	  	}

	  	return $result;
	}

	/**
	 * Create a title that - append a number to end if it exist already.
	 *
	 * @access private
	 */
	private function uniqueBaseTitle($title)
	{
		// Set the base title
		$baseTitle = $title;

    	// Counter
    	$i = 1;

        // Loop and append number
    	while(!empty($this->SQL->SELECT('id')->FROM('posts')->WHERE('title', '=', $title)->ROW()))
    	{
    		$title = preg_replace('/(' . $baseTitle . ")(-\d+)/", '$1' . '', $title) . '-' . $i;
    		$i++;
    	}

    	return $title;
	}

	/**
	 * Convert a title to a slug with permalink structure.
	 *
	 * @param  string       $title        The title of the article
	 * @param  string       $categorySlug The category slug
	 * @param  string       $authorSlug   The author's slug
	 * @param  int          $created      A unix timestamp of when the article was created
	 * @param  string|false $_slug        Existing slug - may or may not be set
	 * @return string       The slug to the article
	 */
	private function titleToSlug($title, $categorySlug, $authorSlug, $created, $type, $_slug)
	{
		// Custom posts have their own route, thus their own slug structure
	  	if ($type === 'page')
	  	{
	  		if ($_slug)
	  		{
	  			return Str::slug($_slug) . '/';
	  		}

	  		return Str::slug($title) . '/';
	  	}
	  	elseif ($type === 'post')
	  	{
	  		$format = $this->config->get('cms.permalinks');
	  	}
	  	else
	  	{
	  		if ($this->config->get('cms.custom_posts.' . $type))
	  		{
	  			$format = $this->config->get('cms.custom_posts.' . $type);
	  		}
	  		else
	  		{
	  			return Str::slug($title) . '/';
	  		}
	  	}

	  	$dateMap =
	  	[
	  		'year'     => 'Y',
	  		'month'    => 'm',
	  		'day'      => 'd',
	  		'hour'     => 'h',
	  		'minute'   => 'i',
	  		'second'   => 's',
	  	];
	  	$varMap =
	  	[
	  		'postname' => Str::slug($title),
	  		'category' => $categorySlug,
	  		'author'   => $authorSlug,
	  	];

	  	$slug = '';

	  	$slugPieces   = !$_slug ? [] : explode('/', trim($_slug, '/'));
	  	$formatPieces = explode('/', $format);

	  	// if the slug is being set pragmatically
	  	// e.g $post->slug = 'foobar'; $post->save();
	  	// Then the slug pieces should always be the postname
	  	// and $slugPieces should have only 1 item

	  	foreach ($formatPieces as $i => $key)
	  	{
	  		if (isset($dateMap[$key]))
	  		{
	  			$slug .= date($dateMap[$key], $created) . '/';
	  		}
	  		elseif (isset($varMap[$key]))
	  		{
	  			if ($key === 'postname')
	  			{
	  				if (count($slugPieces) === 1)
	  				{
	  					$slug .= Str::slug($slugPieces[0]) . '/';
	  				}
	  				else
	  				{
	  					$slug .= $varMap[$key] . '/';
	  				}
	  			}

	  			// Nested categories
	  			elseif ($key === 'category')
	  			{
	  				$category = $this->categoryProvider->byKey('slug', $varMap['category'], true);

	  				if (!$category->parent())
	  				{
	  					$slug .= $varMap[$key] . '/';
	  				}
	  				else
	  				{
	  					$slug .= $this->the_category_slug($category) . '/';
	  				}
	  			}
	  			else
	  			{
	  				$slug .= $varMap[$key] . '/';
	  			}
	  		}
	  		else
	  		{
	  			$slug .= $key . '/';
	  		}
	  	}

	  	$slug = trim($slug, '/') . '/';

	  	return $slug;

	}

    /**
     * Returns the category slug with nested.
     *
     * @access  public
     * @param  \kanso\cms\wrappers\Category|null $category Category wrapper
     * @return string
     */
    private function the_category_slug(Category $category = null): string
    {
    	$slugs  = [];
    	$parent = $category->parent();

	    if ($parent)
	    {
	    	$slugs[] = $category->slug;

	    	while ($parent)
	    	{
	    		$slugs[] = $parent->slug;
	    		$parent  = $parent->parent();
	    	}

	    	$slugs = array_reverse($slugs);

	    	return trim(implode('/', $slugs), '/');
	    }

	    return $category->slug;
    }
}
