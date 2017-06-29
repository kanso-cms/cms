<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\framework\database\query\Builder;
use kanso\framework\config\Config;
use kanso\framework\utility\Str;
use kanso\framework\utility\Arr;
use kanso\cms\wrappers\Wrapper;
use kanso\cms\wrappers\providers\Provider;
use kanso\cms\wrappers\providers\TagProvider;
use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\cms\wrappers\providers\MediaProvider;
use kanso\cms\wrappers\providers\CommentProvider;
use kanso\cms\wrappers\Category;
use kanso\cms\wrappers\Tag;
use kanso\cms\wrappers\PostSaver;

/**
 * Category utility wrapper
 *
 * @author Joe J. Howard
 */
class Post extends Wrapper
{
	/**
     * Pending changes that need to be saved
     * 
     * @var array
     */ 
	private $pending = [];

	/**
     * Assoc array of post data
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
		'category_id' => 1,
		'thumbnail_id'     => null,
		'comments_enabled' => false,

		# Joins
		'tags' 	      => null,
		'category'    => null,
		'author'      => null,
		'comments'    => null,
		'thumbnail'   => null,
		'content'     => null,
	];

	private $tagProvider;

    private $categoryProvider;

    private $mediaProvider;

    private $userProvider;

    private $commentProvider;

    private $config;

	/**
     * Override inherited constructor
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

        if (isset($this->data['tags']) && !empty($this->data['tags']))
        {
        	$this->setPending('tags', $this->data['tags']);
        	unset($this->data['tags']);
        }
        else if (isset($this->data['category']) && !empty($this->data['category']))
        {
        	$this->setPending('category', $this->data['category']);
        	unset($this->data['category']);
        }
        else if (isset($this->data['author']) && !empty($this->data['author']))
        {
        	$this->setPending('author', $this->data['author']);
        	unset($this->data['author']);
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
		if ($key === 'category')
		{
			return $this->getTheCategory();
		}
		else if ($key === 'tags')
		{
			return $this->getTheTags();
		}
		else if ($key === 'author')
		{
			return $this->getTheAuthor();
		}
		else if ($key === 'content')
		{
			return $this->getTheContent();
		}
		else if ($key === 'comments')
		{
			return $this->getTheComments();
		}
		else if ($key === 'excerpt')
		{
			return urldecode($this->data['excerpt']);
		}
		else if ($key === 'meta')
		{
			return unserialize($this->data['meta']);
		}
		else if ($key === 'thumbnail')
		{
			$this->getTheThumbnail();
		}
		else if (array_key_exists($key, $this->data))
		{
			return $this->data[$key];
		}
		else
		{
			return null;
		}	
	}

	/**
	 * Set a value by key
	 *
	 * @access public
	 * @param  string $key   Key to save value to
	 * @param  mixed  $value Value to save
	 */
	public function __set(string $key, $value)
	{
		if ($key === 'tags')
		{
			$this->setPending('tags', $value);
		}
		else if ($key === 'category')
		{
			$this->setPending('category', $value);
		}
		else if ($key === 'author')
		{
			$this->setPending('author', $value);
		}
		else if ($key === 'excerpt')
		{
			$this->data['excerpt'] = urlencode($value);
		}
		else if ($key === 'content')
		{
			$this->data['content'] = urlencode($value);
		}
		else if ($key === 'meta')
		{
			$this->data['meta'] = serialize($value);
		}
		else if (array_key_exists($key, $this->data))
		{
			$this->data[$key] = $value;
		}
	}

	/**
	 * Set a pending key/value on a join table that needs to be saved
	 *
	 * @access public
	 * @param  string $key   Key to set
	 * @param  mixef  $value Value to save
	 */
	private function setPending(string $key, $value)
	{
		$this->pending[$key] = $value;
	}

	/**
	 * Get the category row
	 *
	 * @access private
	 * @return array
	 */
	private function getTheCategory()
	{
		if (!empty($this->data['category_id']))
		{
			if (is_null($this->data['category']))
			{
				$this->data['category'] = $this->categoryProvider->byId($this->data['category_id']);
			}
		}
		else
		{
			$this->data['category'] = $this->categoryProvider->byId(1);
		}

		return $this->data['category'];
	}

	/**
	 * Get the array of tag rows
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
		else
		{
			$this->data['tags'] = [$this->tagProvider->byId(1)];
		}

		return $this->data['tags'];
	}

	/**
	 * Get the post content
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
		else
		{
			$this->data['content'] = '';
		}

		return urldecode($this->data['content']);
	}

	/**
	 * Get the post author
	 *
	 * @access private
	 * @return array
	 */
	private function getTheAuthor()
	{
		if (!empty($this->data['author_id']))
		{
			if (is_null($this->data['author']))
			{
				$this->data['author'] = $this->userProvider->byId($this->data['author_id']);
			}
		}
		else
		{
			$this->data['author'] = $this->userProvider->byId(1);
		}

		return $this->data['author'];
	}

	/**
	 * Get the post thumbnail
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
	 * Get the post thumbnail
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

		# If the category doesn't exist - create it
		$row['category']    = $this->createCategory($row['category']);
		$row['category_id'] = $row['category']->id;

		# If the tags don't exist - create them
		$row['tags'] = $this->createTags($row['tags']);

		# Make sure there is a valid author
		$row['author']    = $this->getTheAuthor();
		$row['author_id'] = $row['author']->id;

		# Get the content
		$row['content'] = $this->getTheContent();

		# Validate the title
		$row['title'] = trim($row['title']);
		if (empty($row['title']))
		{
			$row['title'] = 'Untitled';
		}

		if ($newPost || $row['title'] === 'Untitled')
		{
			$row['title'] = $this->uniqueBaseTitle($row['title']);
		}

		# Sanitize the thumbnail
		$row['thumbnail_id'] = intval($row['thumbnail_id']);
		if ($row['thumbnail_id'] === 0)
		{
			$row['thumbnail_id'] = NULL;
		}
		
		# Create a slug based on the category, tags, slug, author
		$row['slug'] = $this->titleToSlug($row['title'], $row['category']->slug, $row['author']->slug, $row['created'], $row['type']);

		# Sanitize comments_enabled
		$row['comments_enabled'] = boolval($row['comments_enabled']);
	
		# Remove joined rows so we can update/insert
		$insertRow = Arr::unsets(['thumbnail', 'tags', 'category', 'content', 'comments', 'author'], $row);

		# Insert a new article
		if ($newPost)
		{
			unset($insertRow['id']);

			$this->SQL->INSERT_INTO('posts')->VALUES($insertRow)->QUERY();

			$row['id'] = intval($this->SQL->connection()->lastInsertId());
		}

		# Or update an existing row
		else
		{
			$this->SQL->UPDATE('posts')->SET($insertRow)->WHERE('id', '=', $row['id'])->QUERY();
			
			$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();
			
			$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();
		}

		# Join the tags
		foreach ($row['tags'] as $tag)
		{
			$this->SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $row['id'], 'tag_id' => $tag->id])->QUERY();
		}

		# Join the content
		$this->SQL->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $row['id'], 'content' => $row['content']])->QUERY();

		$this->data = $row;

		$this->pending = [];

		# return the row data
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
			
			$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $this->data['id'])->QUERY();
			
			$this->SQL->DELETE_FROM('posts')->WHERE('id', '=', $this->data['id'])->QUERY();

			return true;
		}
		
		return false;
	}

	/**
	 * Create a category if it doesn't exist already
	 *
	 * @param  mixed $category Category name, id or category wrapper
	 * @return kanso\cms\wrappers\Category             
	 */
	private function createCategory($category): Category
	{
		if ($category instanceOf Category)
        {
        	return $category;
        }
        else if (empty($category))
        {
        	return $this->categoryProvider->byId(1);
        }
        else if (is_integer($category))
        {
        	return $this->categoryProvider->byId($category);
        }

        $cat = $this->categoryProvider->byKey('name', $category, true);

	  	if ($cat)
	  	{
	  		return $cat;
	  	}

	  	return $this->categoryProvider->create([
	  		'name' => $category,
	  		'slug' => Str::slug($category),
	  	]);
	}

	/**
	 * Creates tags if they don't already exist
	 *
	 * @access private
	 * @param  mixed $category tag names, or tag wrapper
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
	  		if ($tag instanceOf Tag)
	        {
	        	$result[] = $tag;
	        }
	        else if (is_string($tag))
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
	 * Create a title that - append a number to end if it exist already
	 *
	 * @access private
	 */
	private function uniqueBaseTitle($title)
	{
		# Set the base title
		$baseTitle = $title;
    	
    	# Counter
    	$i = 1;

        # Loop and append number
    	while(!empty($this->SQL->SELECT('*')->FROM('posts')->WHERE('title', '=', $title)->ROW()))
    	{
    		$title = preg_replace("/(".$baseTitle.")(-\d+)/", "$1"."", $title).'-'.$i;
    		$i++;
    	}

    	return $title;
	}

		/**
	 * Convert a title to a slug with permalink structure
	 *
	 * @param  string    $title             The title of the article
	 * @param  string    $categorySlug      The category slug
	 * @param  string    $authorSlug        The author's slug
	 * @param  int       $created           A unix timestamp of when the article was created
	 * @return string                       The slug to the article             
	 */
	private function titleToSlug($title, $categorySlug, $authorSlug, $created, $type) 
	{
		# Custom posts have their own route, thus their own slug structure
	  	if ($type === 'page')
	  	{
	  		return Str::slug($title).'/';
	  	}
	  	else if ($type === 'post')
	  	{
	  		$format = $this->config->get('cms.permalinks');
	  	}
	  	else {
	  		if ($this->config->get('cms.custom_posts.'.$type))
	  		{
	  			$format = $this->config->get('cms.custom_posts.'.$type);
	  		}
	  		else
	  		{
	  			return Str::slug($title).'/';
	  		}
	  	}

	  	$dateMap = [
	  		'year'     => 'Y',
	  		'month'    => 'm',
	  		'day'      => 'd',
	  		'hour'     => 'h',
	  		'minute'   => 'i',
	  		'second'   => 's',
	  	];
	  	$varMap  = [
	  		'postname' => Str::slug($title),
	  		'category' => $categorySlug,
	  		'author'   => $authorSlug,
	  	];
	  	$slug = '';
	  	$urlPieces = explode('/', $format);
	  	foreach ($urlPieces as $key) {
	  		if (isset($dateMap[$key])) {
	  			$slug .= date($dateMap[$key], $created).'/';
	  		}
	  		else if (isset($varMap[$key])) {
	  			$slug .= $varMap[$key].'/';
	  		}
	  		else {
	  			$slug .= $key.'/';
	  		}
	  	}
	  	$slug = trim($slug, '/').'/';
	  	return $slug;

	}
}