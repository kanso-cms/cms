<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Posts;

use Kanso\Framework\Database\Query\Builder;
use Kanso\CMS\Wrappers\Post;
use Kanso\Posts\PostProvider;
use Kanso\Framework\Utility\Arr;

/**
 * Posts manager class
 *
 * @author Joe J. Howard
 */
class Bookkeeper
{
	/**
     * SQL query builder instance
     * 
     * @var \Kanso\Framework\Database\Query\Builder
     */ 
    private $SQL;

    /**
     * Post provider
     * 
     * @var \Kanso\Posts\PostProvider
     */ 
    private $postProvider;

    /**
   	 * Constructor
   	 *
   	 * @access public
     * @param  \Kanso\Framework\Database\Query\Builder $SQL SQL query builder instance
     */
	public function __construct(Builder $SQL, PostProvider $postProvider)
	{
		$this->SQL = $SQL;

		$this->postProvider = $postProvider;
	}

	/**
   	 * Get the post provider
   	 *
   	 * @access public
     * @return \Kanso\Posts\PostProvider
     */
	public function getPostProvider(): PostProvider
	{
		return $this->postProvider;
	}

	/**
   	 * Save a new and or existing article
   	 *
     * @param  array $post Array of post args
     * @return bool
     */
	public function savePost(array $rowData)
	{
		# Is this a new or existing article
		$newPost = true;

		if (isset($rowData['id']) && $rowData['id'] > 0)
		{
			$newPost = false;
			
			$post['modified'] = time();
		}


		# If the category doesn't exist - create it
		$rowData['category'] = $this->createCategory($rowData['category']);
		$rowData['category_id'] = $rowData['category']['id'];

		# If the tags don't exist - create them
		$rowData['tags'] = $this->createTags($rowData['tags']);

		# Make sure there is a valid author
		$rowData['author'] = $this->getAuthor($rowData['author']);
		$rowData['author_id'] = $rowData['author']['id'];

		# Validate the title
		$rowData['title'] = trim($rowData['title']);
		if (empty($rowData['title'])) $rowData['title'] = 'Untitled';

		# Sanitize the thumbnail
		$rowData['thumbnail_id'] = intval($rowData['thumbnail_id']);
		if ($rowData['thumbnail_id'] === 0) $rowData['thumbnail_id'] = NULL;

		# Figure out if the title needs to be changed
		if ($newArticle) {
			$rowData['title'] = $this->uniqueBaseTitle($rowData['title']);
		}
		else if ($rowData['title'] === 'Untitled' && !$newArticle) {
			$titleExists = $this->SQL->SELECT('title')->FROM('posts')->where('title', '=', $rowData['title'])->AND_WHERE('id', '!=', $rowData['id'])->FIND();
			if ($titleExists) $rowData['title'] = $this->uniqueBaseTitle('Untitled');
		}
		
		# Create a slug based on the category, tags, slug, author
		$rowData['slug'] = $this->titleToSlug($rowData['title'], $rowData['category']['slug'], $rowData['author']['slug'], $rowData['created'], $rowData['type']);

		# Sanitize comments_enabled
		$rowData['comments_enabled'] = boolval($rowData['comments_enabled']);
	
		# Remove joined rows so we can update/insert
		$insertRow = Arr::unsets(['thumbnail', 'tags', 'category', 'content', 'comments', 'author'], $rowData);

		# Insert a new article
		if ($newArticle) {
			unset($insertRow['id']);
			$this->SQL->INSERT_INTO('posts')->VALUES($insertRow)->QUERY();
			$rowData['id'] = intval(\Kanso\Kanso::getInstance()->Database->lastInsertId());
		}
		# Or update an existing row
		else {
			$this->SQL->UPDATE('posts')->SET($insertRow)->WHERE('id', '=', $rowData['id'])->QUERY();
			$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $rowData['id'])->QUERY();
			$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $rowData['id'])->QUERY();
		}

		# Join the tags
		foreach ($rowData['tags'] as $tag) {
			$this->SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $rowData['id'], 'tag_id' => $tag['id']])->QUERY();
		}

		# Join the content
		$this->SQL->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $rowData['id'], 'content' => $rowData['content']])->QUERY();

		# return the row data
		return $rowData;
	}

    /**
   	 * Delete an article
   	 *
     * @param  int    $articleID   The article id from the database to be deleted
     */
    public function delete($articleID) 
    {

    	# Convert the article ID to an integer
  		$articleID = (int)$articleID;

        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

    	# Find the existing article
    	$articleRow = $this->SQL->SELECT('*')->FROM('posts')->WHERE('id', '=', $articleID)->ROW();

    	# If it doesn't exist return false
    	if (!$articleRow || empty($articleRow)) return false;

    	# Remove comments associated with the article
    	$this->SQL->DELETE_FROM('comments')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Remove the tags associated with the article
    	$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Remove the content associated with the article
    	$this->SQL->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Clear the cache
    	\Kanso\Kanso::getInstance()->Cache->clear($articleRow['slug']);

    	# Delete the article entry
    	$this->SQL->DELETE_FROM('posts')->WHERE('id', '=', $articleID)->QUERY();

    	return true;

    }

    /********************************************************************************
	* TAXONOMY METHODS
	*******************************************************************************/
	
	/**
	 * Change a tag or categories slug/and/or name
	 *
	 * @param  int        $tagID        The tag id to remove
	 * @param  string     $tagType      'category' or 'tag'
	 * @param  string     $slug         The tags slug
	 * @param  string     $name         The tags name
	 * @return string|boolean
	*/
	public function editTaxonomy($tagID, $tagType, $slug, $name)
	{

		# Convert the article ID to an integer
  		$tagID = (int)$tagID;

        # Get a new Query Builder
        $Query =  \Kanso\Kanso::getInstance()->Database->Builder();

		# Can't change 'Untagged' or 'Uncategorized'
		if ($tagID === 1) return false;

		$table = $tagType === 'tag' ? 'tags' : 'categories';
		$slug  = \Kanso\Utility\Str::slug($slug);

		# Get the tag row
		$tagRow = $this->SQL->SELECT('*')->FROM($table)->WHERE('id', '=', $tagID)->FIND();

		# If it doesn't exist return false
    	if (!$tagRow) return false;

    	# If no changes are needed return true
    	if ($tagRow['slug'] === $slug && $tagRow['name'] === $name) return true;

    	# Get the tag based on the new slug and name
    	$slugRow = $this->SQL->SELECT('*')->FROM($table)->WHERE('slug', '=', $slug)->FIND();
    	$nameRow = $this->SQL->SELECT('*')->FROM($table)->WHERE('name', '=', $name)->FIND();
		
		# If there is another tag with the same slug - return false;
    	if ($slugRow && (int)$slugRow['id'] !== $tagID) return 'slug_exists';

    	# If there is another tag with the same name - return false;
    	if ($nameRow && (int)$nameRow['id'] !== $tagID) return  'name_exists';
 		
    	# Update the tag/category
 		$this->SQL->UPDATE($table)->SET(['name' => $name, 'slug' => $slug])->WHERE('id', '=', (int)$tagRow['id'])->QUERY();

 		# Update all the permalinks
 		if ($tagType === 'category') {
 			$taxPosts = $this->SQL->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$tagID)->FIND_ALL();
 			if ($taxPosts) {
 				foreach ($taxPosts as $post) {
 					$this->updatePostPermalink($post['id']);
 				}
 			}
 		}
 		else {
 			# Find articles from tag
    		$tagPosts = $this->SQL->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tagID)->FIND_ALL();

			# If the tag has articles, loop through the articles
			# If an article will be left with no tags, set it as untagged
			if ($tagPosts) {
				foreach ($tagPosts as $post) {
					$this->updatePostPermalink($post['id']);
				}

			}
 		}

    	return true;
	} 

	/**
	 * Clear or Delete a tag or category
	 *
	 * @param  int        $tagID        The tag id to remove
	 * @param  string     $tagType      'category' or 'tag'
	 * @param  boolean    $deleteTag    Should the tag be deleted after clearing it
	 * @return string|boolean
	*/
	public function clearTaxonomy($tagID, $tagType, $deleteTag = false) 
	{

		# Convert the article ID to an integer
  		$tagID = (int)$tagID;

       	# Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

		# Can't or clear delete 'Untagged' or 'Uncategorized'
		if ($tagID === 1) return false;

		# If this is a tag delete. Note tags have a junction table 
		# i.e many posts to 1 tag.
		if ($tagType === 'tag') {

			# Get the tag row
			$tagRow = $this->SQL->SELECT('*')->FROM('tags')->WHERE('id', '=', (int)$tagID)->ROW();
			
			# If it doesn't exist return false
    		if (!$tagRow || empty($tagRow)) return false;

    		# Find articles from tag
    		$tagArticles = $this->SQL->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tagID)->FIND_ALL();

			# If the tag has articles, loop through the articles
			# If an article will be left with no tags, set it as untagged
			if ($tagArticles && !empty($tagArticles)) {

				foreach ($tagArticles as $article) {
					$articleTags = $this->SQL->SELECT('*')->FROM('tags_to_posts')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
					if (count($articleTags) === 1) {
						$this->SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => (int)$article['id'], 'tag_id' => 1])->QUERY();
					}
				}

			}

			# Remove joins
			$this->SQL->DELETE_FROM('tags_to_posts')->WHERE('tag_id', '=', (int)$tagID)->QUERY();

			# Delete the tag
			if ($deleteTag) $this->SQL->DELETE_FROM('tags')->WHERE('id', '=', (int)$tagID)->QUERY();

			return true;	
		}

		# Otherwise if this is a category delete
		else if ($tagType === 'category') {


			# Get the tag row
			$catRow = $this->SQL->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$tagID)->FIND();
			
			# If it doesn't exist return false
    		if (!$catRow || empty($catRow)) return false;

    		# Find articles from tag
    		$catArticles = $this->SQL->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$tagID)->FIND_ALL();

			# If the tag has articles, loop through the articles
			# Loop through articles and set the category to id 1
    		if (!empty($catArticles)) {

    			foreach ($catArticles as $article) {
    				$this->SQL->UPDATE('posts')->SET(['category_id' => 1])->WHERE('id', '=', (int)$article['id'])->QUERY();
				}
    		}

			# Delete the category
			if ($deleteTag) $this->SQL->DELETE_FROM('categories')->WHERE('id', '=', (int)$tagID)->QUERY();

			return true;
		}

		return false;
	}

	/********************************************************************************
	* PRIVATE HELPER METHODS
	*******************************************************************************/

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
	  	$config = \Kanso\Kanso::getInstance()->Config;
		# Custom posts have their own route, thus their own slug structure
	  	if ($type === 'page') {
	  		return \Kanso\Utility\Str::slug($title).'/';
	  	}
	  	else if ($type === 'post') {
	  		$format = $config['KANSO_PERMALINKS']; 
	  	}
	  	else {
	  		if (isset($config['KANSO_CUSTOM_POSTS'][$type])) {
	  			$format = $config['KANSO_CUSTOM_POSTS'][$type];
	  		}
	  		else {
	  			return \Kanso\Utility\Str::slug($title).'/';
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
	  		'postname' => \Kanso\Utility\Str::slug($title),
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

	/**
	 * Create a category if it doesn't exist already
	 *
	 * @param  string   $category       Category name
	 * @return array             
	 */
	private function createCategory($category) 
	{
		$default = [
	  		'id'   => 1,
	  		'name' => 'Uncategorized',
	  		'slug' => 'uncategorized',
	  	];
	  	
        if (empty($category)) return $default;

         # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

        # If the category is an array get the name
        # This is only incase it was deleted previously
        if (is_array($category) && isset($category['name'])) $category = $category['name'];

        # Check if the category exists
	  	$catRow = $this->SQL->SELECT('*')->FROM('categories')->WHERE('name', '=', $category)->ROW();

	  	# If it exists return it
	  	if ($catRow) return $catRow;

	  	# Otherwise create a new category and return it
	  	$row = [
	  		'name' => $category,
	  		'slug' => \Kanso\Utility\Str::slug($category),
	  	];
	  	$this->SQL->INSERT_INTO('categories')->VALUES($row)->QUERY();

	  	$row['id'] = intval(\Kanso\Kanso::getInstance()->Database->lastInsertId());

	  	return $row;
	}

	/**
	 * Create a tag if it doesn't exist already
	 *
	 * @param  array   $tags       Array of tag names to be created
	 * @return array             
	 */
    private function createTags($tags) 
    {
	  	# Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

        # Set an empty list
	   	$tagsList = [[
		  		'id' => 1,
				'name' => 'Untagged',
				'slug' => 'untagged'
		]];

	   	if (is_string($tags)) $tags = array_filter(array_map('trim', explode(',', $tags)));

	   	if (empty($tags)) return $tagsList;

	  	foreach ($tags as $tag) {
	  		
	  		if (is_array($tag) && isset($tag['name'])) $tag = $tag['name'];
	  		
	  		if (ucfirst($tag) === 'Untagged') continue;
	  		
	  		$tagRow = $this->SQL->SELECT('*')->FROM('tags')->WHERE('name', '=', $tag)->FIND();
	  		
	  		if ($tagRow) {
	   			$tagsList[] = $tagRow;
	   		}
	   		else {
	   			$row = [
			  		'name' => $tag,
			  		'slug' => \Kanso\Utility\Str::slug($tag),
			  	];
			  	$this->SQL->INSERT_INTO('tags')->VALUES($row)->QUERY();
			  	$row['id']  = intval(\Kanso\Kanso::getInstance()->Database->lastInsertId());
			  	$tagsList[] = $row;
	   		}
	  	}

	  	# If there's more than 1 tag remove untagged
	  	if (count($tagsList) > 1) array_shift($tagsList);

   		return $tagsList;
   	}

   	/**
	 * Make sure the row has an author from the DB
	 *
	 * @param  mixed   $_author       name or id of author or null
	 * @return array             
	 */
   	private function getAuthor($_author)
   	{
   		# Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

   		$author = [];
   		if (is_numeric($_author)) {
			$author = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', $_author)->ROW();
		}
   		else if (is_string($_author)) {
			$author = $this->SQL->SELECT('*')->FROM('users')->WHERE('name', '=', $_author)->ROW();
		}
		if (empty($author)) {
			$author = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', 1)->ROW();
		}
		return $author;
   	}

	/**
	 * Create a title that - append a number to end if it exist already
	 *
	 * @param  string    $path    The input title
	 * @return string             The output title
	 */
	private function uniqueBaseTitle($title)
	{

		# Set the base title
		$baseTitle = $title;
    	
    	# Counter
    	$i = 1;

    	# Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

        # Loop and append number
    	while(!empty($this->SQL->SELECT('*')->FROM('posts')->WHERE('title', '=', $title)->FIND())) {
    		$title = preg_replace("/(".$baseTitle.")(-\d+)/", "$1"."", $title).'-'.$i;
    		$i++;
    	}

    	return $title;
	}

	/**
     * Update a post's permalink
     */
    private function updatePostPermalink($id) 
    {
    	$query = \Kanso\Kanso::getInstance()->Database->Builder();

        # Get the entry
        $post = $this->existing($id);
       
        # Loop through the articles and update the slug and permalink
        if ($post) {
            $newSlug = $this->titleToSlug($post->title, $post->category['slug'], $post->author['slug'], $post->created, $post->type);
            $query->UPDATE('posts')->SET(['slug' => $newSlug])->WHERE('id', '=', (int)$post->id)->QUERY();
        }
    }

}