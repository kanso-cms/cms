<?php

namespace Kanso\Articles;

/**
 * Articles Manager
 *
 * This class serves as a manager for various functions related to articles
 * like delete, save, publish etc... It's used throughout the admin panel
 * to manage articles. Note that it is a static class so methods can be used
 * statically from the router.
 *
 */
class Bookkeeper
{

	public function __construct()
	{

	}

	/********************************************************************************
	* ARTICLE METHODS
	*******************************************************************************/
	
	/**
   	 * Get a new article object
   	 *
     * @return \Kanso\Articles\Article
     */
	public function create()
	{
		return new \Kanso\Articles\Article();
	}

	/**
   	 * Get an existing article by id
   	 *
   	 * @param  int     $id     Kanso\Articles\Article
     * @return \Kanso\Articles\Article
     */
	public function existing($id)
	{
		# Prevalidate the article exists
		$row = \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('*')->FROM('posts')->WHERE('id', '=', (int) $id)->ROW();
		if (!$row) return false;
		return new \Kanso\Articles\Article($row);
	}

	/**
   	 * Get an existing article by key/value
   	 *
   	 * @param  string  $index        Table and column in dot notation e.g posts.id or autor.id or tags.name
   	 * @param  mixed   $value        The value of the column
   	 * @param  mixed   $published    Is the article published?
     * @return array   [Kanso\Articles\Article]
     */
	public function byIndex($index, $value, $published = true)
	{

		if ($index === 'id') $index = 'posts.id';

		# Get a new Builder 
        $Query =  \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('posts.*')->FROM('posts')->WHERE($index, '=', $value);

        $Query->LEFT_JOIN_ON('users', 'users.id = posts.author_id');

        $Query->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');

        $Query->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id');

        $Query->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');

        $Query->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');

        $Query->GROUP_BY('posts.id');

        if ($published) $Query->AND_WHERE('status', '=', 'published');

        # Find the articles
        $articles = $Query->FIND_ALL();
		
		$postObjs = [];

		if ($articles) {
			foreach ($articles as $article) {
				$postObjs[] = new Kanso\Articles\Article($article);
			}
		}
		return $postObjs;
	}


	/**
   	 * Save a new and or existing article
   	 *
     * @param  array|object     $rowData     Kanso\Articles\Article->tmpRow|Kanso\Articles\Article->tmpRow
     * @return array|boolean
     */
	public function saveArticle($rowData)
	{
		# Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database->Builder();

		# Is this a new or existing article
		$newArticle = true;
		if (isset($rowData['id']) && !empty($rowData['id'])) {
			$rowData['id']       = intval($rowData['id']);
			$newArticle          = false;
			$rowData['modified'] = time();
		}

		# Save the initial slug
		$initialSlug = $rowData['slug'];

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
			$titleExists = $Query->SELECT('title')->FROM('posts')->where('title', '=', $rowData['title'])->AND_WHERE('id', '!=', $rowData['id'])->FIND();
			if ($titleExists) $rowData['title'] = $this->uniqueBaseTitle('Untitled');
		}
		
		# Create a slug based on the category, tags, slug, author
		$rowData['slug'] = $this->titleToSlug($rowData['title'], $rowData['category']['slug'], $rowData['author']['slug'], $rowData['created'], $rowData['type']);

		# Sanitize comments_enabled
		$rowData['comments_enabled'] = boolval($rowData['comments_enabled']);
	
		# Remove joined rows so we can update/insert
		$insertRow = \Kanso\Utility\Arr::unsetMultiple(['tags', 'category', 'content', 'comments', 'author'], $rowData);

		# Insert a new article
		if ($newArticle) {
			unset($insertRow['id']);
			$Query->INSERT_INTO('posts')->VALUES($insertRow)->QUERY();
			$rowData['id'] = intval(\Kanso\Kanso::getInstance()->Database->lastInsertId());
		}
		# Or update an existing row
		else {
			$Query->UPDATE('posts')->SET($insertRow)->WHERE('id', '=', $rowData['id'])->QUERY();
			$Query->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $rowData['id'])->QUERY();
			$Query->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $rowData['id'])->QUERY();
		}

		# Join the tags
		foreach ($rowData['tags'] as $tag) {
			$Query->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $rowData['id'], 'tag_id' => $tag['id']])->QUERY();
		}

		# Join the content
		$Query->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $rowData['id'], 'content' => $rowData['content']])->QUERY();

		# Fire the event
		if ($newArticle) {
			\Kanso\Events::fire('newArticle', $rowData);
		}
		else {
			\Kanso\Events::fire('articleSave', $rowData);
		}

		if ($rowData['status'] === 'published') {
			\Kanso\Events::fire('articlePublish', $rowData);
		}
		
		# If the article is a page and the slug was changed
		# Remove the old slug and add the new one
		if ($rowData['type'] === 'page' && $rowData['slug'] !== $initialSlug) {
			$this->removeFromStaticPages($initialSlug);
			$this->addToStaticPages($rowData['slug']);
		} 

		# return the row data
		return $rowData;
	}


  	/**
   	 * Change an articles status
   	 *
     * @param  int       $articleID   The article id from the database to be deleted
     * @param  string    $status      The article status to change
     * @return string|boolean
     */
  	public function changeStatus($articleID, $status) 
  	{
  		
  		# Convert the article ID to an integer
  		$articleID = (int)$articleID;

        # Get a new Query Builder
        $Query =  \Kanso\Kanso::getInstance()->Database()->Builder();

  		# Find the existing article
    	$articleRow = $Query->SELECT('*')->FROM('posts')->WHERE('id', '=', $articleID)->ROW();

    	# If it doesn't exist return false
    	if (!$articleRow || empty($articleRow)) return false;

    	# Return if nothing needs to be changed
    	if ($articleRow['status'] === $status) return true;

    	# If the article is a published page, update kanso's static pages
    	if ($status === 'published' && $articleRow['type'] === 'page') {
    		$this->addToStaticPages($articleRow['slug']);
    	}
    	if ($status === 'draft' && $articleRow['type'] === 'page') {
    		$this->removeFromStaticPages($articleRow['slug']);
    	}

    	# Save the entry
    	return $Query->UPDATE('posts')->SET(['status' => $status])->WHERE('id', '=', $articleID)->QUERY();
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
    	$articleRow = $Query->SELECT('*')->FROM('posts')->WHERE('id', '=', $articleID)->ROW();

    	# If it doesn't exist return false
    	if (!$articleRow || empty($articleRow)) return false;

    	# Remove comments associated with the article
    	$Query->DELETE_FROM('comments')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Remove the tags associated with the article
    	$Query->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Remove the content associated with the article
    	$Query->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $articleID)->QUERY();

    	# Clear the cache
    	\Kanso\Kanso::getInstance()->Cache->clear($articleRow['slug']);

    	# Delete the article entry
    	$Query->DELETE_FROM('posts')->WHERE('id', '=', $articleID)->QUERY();

    	# If the article was a published page, update kanso's static pages
    	if ($articleRow['type'] === 'page' && $articleRow['status'] === 'published') $this->removeFromStaticPages($articleRow['slug']);

    	# Fire the article delete event
    	\Kanso\Events::fire('articleDelete', $articleRow);

    	return true;

    }

    /**
	 * Batch import an array of articles
	 *
	 * @param  array    $articles    Associative array of the articles
	 * @return string
	*/
	public function batchImport($articles) 
	{

	    # Loop the articles
	  	foreach ($articles as $i => $article) {

	      	# Validate the article's array keys
	  		if (!\Kanso\Utility\Arr::issets(['created', 'modified', 'status', 'type', 'title', 'excerpt', 'category', 'tags', 'content', 'thumbnail_id', 'comments_enabled'], $article )) return "invalid_json";
	  		if (!is_numeric($article['created'])) return "invalid_json";
	  		if (!is_numeric($article['modified'])) return "invalid_json";
	  		if ($article['type'] !== 'page' && $article['type'] !== 'post') return "invalid_json";
	  		if ($article['status'] !== 'published' && $article['status'] !== 'draft') return "invalid_json";

	      	# Sanitize values
	  		$articles[$i]['title']       = filter_var($articles[$i]['title'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['excerpt']     = filter_var($articles[$i]['excerpt'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['category']    = filter_var($articles[$i]['category'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['thumbnail_id']= intval($articles[$i]['thumbnail_id']);
	  		$articles[$i]['tags']  		 = filter_var($articles[$i]['tags'], FILTER_SANITIZE_STRING);
	  		$article['comments_enabled'] = (bool) $article['comments_enabled'];
	  		$articles[$i]['created']     = (int)$articles[$i]['created'];
	  		$articles[$i]['modified']    = (int)$articles[$i]['modified'];
	  		if (isset($articles[$i]['author_id'])) $articles[$i]['author_id'] = (int)$articles[$i]['author_id'];

	  		$post = $this->create();
	  		foreach ($articles[$i] as $key => $value) {
	  			$post->$key = $value;
	  		}

	  		if (!$post->save())  return "invalid_json"; 

	  	}

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
        $Query =  \Kanso\Kanso::getInstance()->Database()->Builder();

		# Can't change 'Untagged' or 'Uncategorized'
		if ($tagID === 1) return false;

		$table = $tagType === 'tag' ? 'tags' : 'categories';
		$slug  = \Kanso\Utility\Str::slugFilter($slug);

		# Get the tag row
		$tagRow = $Query->SELECT('*')->FROM($table)->WHERE('id', '=', $tagID)->FIND();

		# If it doesn't exist return false
    	if (!$tagRow) return false;

    	# If no changes are needed return true
    	if ($tagRow['slug'] === $slug && $tagRow['name'] === $name) return true;

    	# Get the tag based on the new slug and name
    	$slugRow = $Query->SELECT('*')->FROM($table)->WHERE('slug', '=', $slug)->FIND();
    	$nameRow = $Query->SELECT('*')->FROM($table)->WHERE('name', '=', $name)->FIND();
		
		# If there is another tag with the same slug - return false;
    	if ($slugRow && (int)$slugRow['id'] !== $tagID) return 'slug_exists';

    	# If there is another tag with the same name - return false;
    	if ($nameRow && (int)$nameRow['id'] !== $tagID) return  'name_exists';
 		
    	# Update the tag/category
 		$Query->UPDATE($table)->SET(['name' => $name, 'slug' => $slug])->WHERE('id', '=', (int)$tagRow['id'])->QUERY();

 		# Update all the permalinks
 		if ($tagType === 'category') {
 			$taxPosts = $Query->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$tagID)->FIND_ALL();
 			if ($taxPosts) {
 				foreach ($taxPosts as $post) {
 					$this->updatePostPermalink($post['id']);
 				}
 			}
 		}
 		else {
 			# Find articles from tag
    		$tagPosts = $Query->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tagID)->FIND_ALL();

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
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

		# Can't or clear delete 'Untagged' or 'Uncategorized'
		if ($tagID === 1) return false;

		# If this is a tag delete. Note tags have a junction table 
		# i.e many posts to 1 tag.
		if ($tagType === 'tag') {

			# Get the tag row
			$tagRow = $Query->SELECT('*')->FROM('tags')->WHERE('id', '=', (int)$tagID)->ROW();
			
			# If it doesn't exist return false
    		if (!$tagRow || empty($tagRow)) return false;

    		# Find articles from tag
    		$tagArticles = $Query->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tagID)->FIND_ALL();

			# If the tag has articles, loop through the articles
			# If an article will be left with no tags, set it as untagged
			if ($tagArticles && !empty($tagArticles)) {

				foreach ($tagArticles as $article) {
					$articleTags = $Query->SELECT('*')->FROM('tags_to_posts')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
					if (count($articleTags) === 1) {
						$Query->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => (int)$article['id'], 'tag_id' => 1])->QUERY();
					}
				}

			}

			# Remove joins
			$Query->DELETE_FROM('tags_to_posts')->WHERE('tag_id', '=', (int)$tagID)->QUERY();

			# Delete the tag
			if ($deleteTag) $Query->DELETE_FROM('tags')->WHERE('id', '=', (int)$tagID)->QUERY();

			return true;	
		}

		# Otherwise if this is a category delete
		else if ($tagType === 'category') {


			# Get the tag row
			$catRow = $Query->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$tagID)->FIND();
			
			# If it doesn't exist return false
    		if (!$catRow || empty($catRow)) return false;

    		# Find articles from tag
    		$catArticles = $Query->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$tagID)->FIND_ALL();

			# If the tag has articles, loop through the articles
			# Loop through articles and set the category to id 1
    		if (!empty($catArticles)) {

    			foreach ($catArticles as $article) {
    				$Query->UPDATE('posts')->SET(['category_id' => 1])->WHERE('id', '=', (int)$article['id'])->QUERY();
				}
    		}

			# Delete the category
			if ($deleteTag) $Query->DELETE_FROM('categories')->WHERE('id', '=', (int)$tagID)->QUERY();

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
	  		return \Kanso\Utility\Str::slugFilter($title).'/';
	  	}
	  	else if ($type === 'post') {
	  		$format = $config['KANSO_PERMALINKS']; 
	  	}
	  	else {
	  		if (isset($config['KANSO_CUSTOM_POSTS'][$type])) {
	  			$format = $config['KANSO_CUSTOM_POSTS'][$type];
	  		}
	  		else {
	  			return \Kanso\Utility\Str::slugFilter($title).'/';
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
	  		'postname' => \Kanso\Utility\Str::slugFilter($title),
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
	  	$catRow = $Query->SELECT('*')->FROM('categories')->WHERE('name', '=', $category)->ROW();

	  	# If it exists return it
	  	if ($catRow) return $catRow;

	  	# Otherwise create a new category and return it
	  	$row = [
	  		'name' => $category,
	  		'slug' => \Kanso\Utility\Str::slugFilter($category),
	  	];
	  	$Query->INSERT_INTO('categories')->VALUES($row)->QUERY();

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
	  		
	  		$tagRow = $Query->SELECT('*')->FROM('tags')->WHERE('name', '=', $tag)->FIND();
	  		
	  		if ($tagRow) {
	   			$tagsList[] = $tagRow;
	   		}
	   		else {
	   			$row = [
			  		'name' => $tag,
			  		'slug' => \Kanso\Utility\Str::slugFilter($tag),
			  	];
			  	$Query->INSERT_INTO('tags')->VALUES($row)->QUERY();
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
			$author = $Query->SELECT('*')->FROM('users')->WHERE('id', '=', $_author)->ROW();
		}
   		else if (is_string($_author)) {
			$author = $Query->SELECT('*')->FROM('users')->WHERE('name', '=', $_author)->ROW();
		}
		if (empty($author)) {
			$author = $Query->SELECT('*')->FROM('users')->WHERE('id', '=', 1)->ROW();
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
    	while(!empty($Query->SELECT('*')->FROM('posts')->WHERE('title', '=', $title)->FIND())) {
    		$title = preg_replace("/(".$baseTitle.")(-\d+)/", "$1"."", $title).'-'.$i;
    		$i++;
    	}

    	return $title;
	}

	/**
	 * Remove a static page from Kanso's config
	 *
	 * @param  string    $slug    The slug to remove
	 * @return boolean
	 */
	private function removeFromStaticPages($slug)
	{
		# Get the config
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_STATIC_PAGES'];

        # Remove the slug
        foreach ($slugs as $i => $configSlug) {
            if ($configSlug === $slug) unset($slugs[$i]);
        }

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_STATIC_PAGES', array_values($slugs));
	}

	/**
	 * Add a static page to Kanso's config
	 *
	 * @param  string    $slug    The slug to remove
	 * @return boolean
	 */
	private function addToStaticPages($slug)
	{
		 # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_STATIC_PAGES'];

        # Add the slug
        $slugs[] = $slug;

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_STATIC_PAGES', array_unique(array_values($slugs)));
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