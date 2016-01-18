<?php

namespace Kanso\Articles;

/**
 * Articles
 *
 * This class serves as a wrapper around database 
 * post (article) entries. By using this wrapper 
 * class, article data is only retrieved once 
 * it is needed.
 *
 */
class Article
{
	/**
	 * @var array    Associative array of the article data
	 */
	private $row;

	/**
	 * @var array    Associative array of pending article data
	 */
	private $tmpRow;

	/**
	 * @var array    Associative array of default article data
	 */
	private $keys = [

		# Required keys
		'id'          => null,
		'created'     => null,
		'modified'    => null,
		'status'      => 'draft',
		'type'        => 'post',
		'slug'        => '',
		'title'       => 'Untitled',
		'excerpt'     => '',
		'author_id'   => 1,
		'category_id' => null,
		'thumbnail'   => '',
		'comments_enabled' => false,

		# Joins
		'tags' 	   => 'Untagged',
		'category' => 'Uncategorized',
		'author'   => null,
		'content'  => ' ',
		'comments' => null,
	];

	/**
	 * Constructor
	 *
	 * @param   array      $row        Associative array of the article data
	 */
	public function __construct($rowOrId = [])
	{
		$this->keys['created']  = time();
		$this->keys['modified'] = time();

		if (!$rowOrId) {
			$this->row    = $this->keys;
			$this->tmpRow = $this->keys;

		}
		else if (is_array($rowOrId)) {
			$this->row    = $rowOrId;
			$this->tmpRow = $rowOrId;
		}
		else if (is_numeric($rowOrId) || is_int($rowOrId)) {
			$row = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('posts')->WHERE('id', '=', (int)$rowOrId)->ROW();
			$this->row    = $row;
			$this->tmpRow = $row;
		}
	}

	/********************************************************************************
	* PUBLIC GETTERS AND SETTERS
	*******************************************************************************/

	public function __get($key)
	{
		if ($key === 'category') {
			return $this->getTheCategory();
		}
		else if ($key === 'tags') {
			return $this->getTheTags();
		}
		else if ($key === 'author') {
			return $this->getTheAuthor();
		}
		else if ($key === 'content') {
			return $this->getTheContent();
		}
		else if ($key === 'comments') {
			return $this->getTheComments();
		}
		if (array_key_exists($key, $this->row)) return $this->row[$key];
		
		return false;
	}

	public function __set($key, $value)
	{
		if ($key === 'tags') {
			$this->setTheTags($value);
		}
		else if (array_key_exists($key, $this->keys)) {
			$this->tmpRow[$key] = $value;
		}
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->row);
	}

	public function __unset($key)
	{
		if ($key === 'category') {
			$this->tmpRow['category'] = $this->keys['category'];
		}
		else if ($key === 'tags') {
			$this->tmpRow['tags'] = $this->keys['tags'];
		}
		else if ($key === 'author') {
			$this->tmpRow['author'] = $this->getTheAuthor();
		}
		else if (array_key_exists($key, $this->keys)) {
			$this->tmpRow[$key] = null;
		}
	}

	/********************************************************************************
	* GETTERS
	*******************************************************************************/

	private function getTheCategory()
	{
		# If this is an unsaved row, return the temporary value
		if ($this->row['id'] == null) return $this->tmpRow['category'];

		# Only load the category once
		if (!isset($this->row['category'])) {
			$category = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$this->row['category_id'])->ROW();
			$this->row['category'] 	  = $category;
		}
		return $this->row['category'];
	}

	private function getTheTags()
	{
		# If this is an unsaved row, return the temporary value
		if ($this->row['id'] == null) return $this->tmpRow['tags'];

		# Only load the tags once
		if (!isset($this->row['tags'])) {
			$tags = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('post_id', '=', (int)$this->row['id'])->FIND_ALL();
			$this->row['tags'] = $tags;
		}
		return $this->row['tags'];
	}

	private function getTheContent()
	{
		# If this is an unsaved row, return the temporary value
		if ($this->row['id'] == null) return $this->tmpRow['content'];

		# Only load the content once
		if (!isset($this->row['content'])) {
			$content = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', (int)$this->row['id'])->ROW();
			$this->row['content'] = $content['content'];
		}
		return $this->row['content'];
	}


	private function getTheAuthor()
	{
		# If this is an unsaved row, return the initial admin
		if ($this->row['id'] == null) {
			$author = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', 1)->ROW();
			$this->row['author'] = $author;
		}

		# Only load the content once
		if (empty($this->row['author'])) {
			$author = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', (int)$this->row['author_id'])->ROW();
			$this->row['author'] = $author;
		}
		return $this->row['author'];
	}

	private function getTheComments()
	{	
		# If this is an unsaved row, return the initial admin
		if ($this->row['id'] == null) return [];

		# Only load the comments once
		if (empty($this->row['comments'])) {
			$comments = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$this->row['id'])->FIND_ALL();
			$this->row['comments']    = $comments;
			$this->tmpRow['comments'] = $comments;
		}
		return $this->row['comments'];
	}

	/********************************************************************************
	* SETTERS
	*******************************************************************************/

	private function setTheTags($names)
	{
		if (trim($names) === '' || $names == null) {
			$this->tmpRow['tags'] = '';
			return;
		}

		# Get the current tags
		$tags = $this->getTheTags();

		# Explode the tags into an array
		$names = array_filter(array_map('trim', explode(',', $names)));

		# If the current tags are not array, explode them too.
		if (!is_array($tags)) {
			$tags = array_filter(array_map('trim', explode(',', $tags)));
		} 
		# Otherwise only use the tag name, implode and explode them
		else {
			$tags = \Kanso\Utility\Arr::implodeByKey('name', $tags, ', ');
			$tags = array_filter(array_map('trim', explode(',', $tags)));
		}

		# Merge the two arrays
		$tags = array_merge($tags, $names);	

		# If there's more than one tag, remove untagged
		if (count($tags) > 1) {
			foreach ($tags as $i => $tag) {
				if (strtolower($tag) === 'untagged') {
					unset($tags[$i]); 
				}
			}
		}

		$this->tmpRow['tags'] = implode(', ', array_unique($tags));
	}

	/********************************************************************************
	* SAVE THE ROW
	*******************************************************************************/

	public function save()
	{
		# Initialize joins if they have not been already
		$this->getTheCategory();
		$this->getTheTags();
		$this->getTheAuthor();
		$this->getTheContent();

		# Get the bookkeeper
		$bookkeeper = \Kanso\Kanso::getInstance()->Bookkeeper;

		# Update Kanso's static pages if the status has changed 
		# and/or the article type has changed
		if ($this->row['status'] !== $this->tmpRow['status'] && isset($this->row['id'])) {
			$bookkeeper->changeStatus($this->row['id'], $this->tmpRow['status']);
		}

		# If no updates are required return;
		if ($this->tmpRow === $this->row && isset($this->row['id'])) return;

		# Save the article
		$save = $bookkeeper->saveArticle(array_merge($this->row, $this->tmpRow));

		# Merge the results
		if ($save) {
			$save['tags'] = \Kanso\Utility\Arr::implodeByKey('name', $save['tags'], ', ');
			$this->tmpRow = array_merge($this->tmpRow, $save);
			$this->row    = array_merge($this->row, $save);
			return true;
		}
		else {
			return false;
		}
	}

	public function delete()
	{
		# If this is an unsaved article return;
		if ($this->row['id'] !== null) return;

		return \Kanso\Kanso::getInstance()->Bookkeeper->delete($this->row['id']);
	}


}