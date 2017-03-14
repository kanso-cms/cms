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
	 * @var array    Pending data that needs to be saved
	 */
	private $pending = [];

	/**
	 * @var array    Associative array of the article data
	 */
	private $rowData = [
		'id'          => '',
		'created'     => '',
		'modified'    => '',
		'status'      => '',
		'type'        => '',
		'slug'        => '',
		'title'       => '',
		'excerpt'     => '',
		'author_id'   => '',
		'category_id' => '',
		'thumbnail'   => '',
		'comments_enabled' => '',
		
		# Joins
		'tags' 	      => [],
		'category'    => [],
		'author'      => [],
		'content'     => ' ',
		'comments'    => [],
	];

	/**
	 * @var array    Defaults
	 */
	private $defaults = [
		'author_id'   => 1,
		'category_id' => 1,
		'tags' 	      => [['id' => 1, 'name' => 'Untagged']],
		'category'    => ['id' => 1, 'name' => 'Uncategorized'],
	];

	/**
	 * Constructor
	 *
	 * @param   array      $row        Associative array of the article data
	 */
	public function __construct($rowOrId = [])
	{
		$this->rowData['created']  = time();
		$this->rowData['modified'] = time();

		if (is_array($rowOrId) && !empty($rowOrId)) {
			$this->rowData = $rowOrId;
			$this->rowData['tags']     = [];
			$this->rowData['category'] = [];
			$this->rowData['author']   = [];
			$this->rowData['comments'] = [];
			$this->rowData['content']  = ' ';

		}
		else if (is_numeric($rowOrId) || is_int($rowOrId)) {
			$row = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('posts')->WHERE('id', '=', intval($rowOrId))->ROW();
			$this->rowData = $row;
			$this->rowData['tags']     = [];
			$this->rowData['category'] = [];
			$this->rowData['author']   = [];
			$this->rowData['comments'] = [];
			$this->rowData['content']  = ' ';
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
		else if ($key === 'excerpt') {
			return urldecode($this->rowData['excerpt']);
		}
		if (array_key_exists($key, $this->rowData)) return $this->rowData[$key];
		
		return false;
	}

	public function __set($key, $value)
	{
		if ($key === 'tags') {
			$this->setTheTags($value);
		}
		else if ($key === 'category') {
			$this->setTheCategory($value);
		}
		else if ($key === 'author') {
			$this->setTheAuthor($value);
		}
		else if ($key === 'status') {
			$this->setTheStatus($value);
		}
		else if ($key === 'excerpt') {
			$this->rowData['excerpt'] = urlencode($value);
		}
		else if ($key === 'content') {
			$this->rowData['content'] = urlencode($value);
		}
		
		else if (array_key_exists($key, $this->rowData)) {
			$this->rowData[$key] = $value;
		}
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->rowData);
	}

	public function __unset($key)
	{
		if (array_key_exists($key, $this->rowData)) {
			$this->rowData[$key] = '';
		}
	}

	/********************************************************************************
	* GETTERS
	*******************************************************************************/

	private function getTheCategory()
	{
		
		if (!empty($this->rowData['category_id'])) {
			$category = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('categories')->WHERE('id', '=', $this->rowData['category_id'])->ROW();
			if ($category) {
				$this->rowData['category']    = $category;
				$this->rowData['category_id'] = $category['id'];
				return $category;
			}
		}
		return $this->defaults['category'];
	}

	private function getTheTags()
	{
		if (empty($this->rowData['tags']) || !isset($this->rowData['tags'])) {
			if (!empty($this->rowData['id'])) {
				$tags = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('post_id', '=', intval($this->rowData['id']))->FIND_ALL();
				if ($tags) {
					$this->rowData['tags'] = $tags;
					return $tags;
				}
			}
		}
		return $this->defaults['tags'];
	}

	private function getTheContent()
	{
		if (!empty($this->rowData['id'])) {
			$content = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', intval($this->rowData['id']))->ROW();
			$this->rowData['content'] = $content['content'];
			return urldecode($this->rowData['content']);
		}
		else if (!isset($this->rowData['content'])) {
			return '';
		}
		else if ($this->rowData['content'] === ' ') {
			return '';
		}
		return $this->rowData['content'];
	}

	private function getTheAuthor()
	{
		if (!empty($this->rowData['author_id'])) {
			$author = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', $this->rowData['author_id'])->ROW();
			if ($author) {
				$this->rowData['author']    = $author;
				$this->rowData['author_id'] = $author['id'];
				return $author;
			}
		}
		if (empty($this->defaults['author'])) {
			$this->defaults['author'] = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', 1)->ROW();
		}
		return $this->defaults['author'];
	}

	private function getTheComments()
	{
		if (!empty($this->rowData['id'])) {
			$comments = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('comments')->WHERE('post_id', '=', intval($this->rowData['id']))->FIND_ALL();
			$this->rowData['comments'] = $comments;
		}
		return $this->rowData['comments'];
	}

	/********************************************************************************
	* SETTERS
	*******************************************************************************/

	private function setTheCategory($category)
	{
		$this->pending['category'] = $category;
	}

	private function setTheTags($tags)
	{
		$this->pending['tags'] = $tags;
	}

	private function setTheAuthor($author)
	{
		$this->pending['author'] = $author;
	}

	private function setTheStatus($status)
	{
		$this->pending['status'] = $status;
	}

	/********************************************************************************
	* SAVE THE ROW
	*******************************************************************************/

	public function save()
	{
		# Row to save
		$to_save = $this->rowData;
			
		# Pending stuff that needs to be joined/created
		if (isset($this->pending['category'])) {
			$to_save['category'] = $this->pending['category'];
		}
		if (isset($this->pending['tags'])) {
			$to_save['tags'] = $this->pending['tags'];
		}
		if (isset($this->pending['author'])) {
			$to_save['author'] = $this->pending['author'];
		}
		if (isset($this->pending['status'])) {
			$to_save['status'] = $this->pending['status'];
		}

		# Get the bookkeeper
		$bookkeeper = \Kanso\Kanso::getInstance()->Bookkeeper;

		# Update Kanso's static pages if the status has changed 
		# and/or the article type has changed
		if ($this->rowData['status'] !== $to_save['status'] && isset($this->rowData['id'])) {
			$bookkeeper->changeStatus($this->rowData['id'], $to_save['status']);
		}

		# Save the article
		$save = $bookkeeper->saveArticle($to_save);

		# Merge the results
		if ($save) {
			$this->rowData = $save;
			return true;
		}
		return false;
	}

	public function delete()
	{
		# If this is an unsaved article return;
		if ($this->row['id'] !== null) return;

		return \Kanso\Kanso::getInstance()->Bookkeeper->delete($this->row['id']);
	}


}