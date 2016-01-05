<?php

namespace Kanso\Admin\Utility;

/**
 * Articles Manager
 *
 * This class serves as a manager for various functions related to articles
 * like delete, save, publish etc... It's used throughout the admin panel
 * to manage articles. Note that it is a static class so methods can be used
 * statically from the router.
 *
 */
class articlesManager
{

  	/**
  	 * @var \Kanso\Kanso
  	 */
  	protected static $Kanso;

  	/**
   	 * Change an articles status
   	 *
     * @param  int       $articleID   The article id from the database to be deleted
     * @param  string    $status      The article status to change
     * @return string|boolean
     */
  	public static function changeArticleStatus($articleID, $status) 
  	{
  		
  		# Convert the article ID to an integer
  		$articleID = (int)$articleID;

  		# Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

  		# Find the existing article
    	$articleRow = $Query->getArticleById($articleID);

    	# If it doesn't exist return false
    	if (!$articleRow || empty($articleRow)) return false;

    	# Return if nothing needs to be changed
    	if ($articleRow['status'] === $status) return true;

    	# If the article is a published page, update kanso's static pages
    	\Kanso\Admin\Utility\settingsManager::addToStaticPages($articleRow['slug']);

    	# Save the entry
    	$save = $Query->UPDATE('posts')->SET(['status' => $status])->WHERE('id', '=', $articleID)->QUERY();

    	return 'valid';

  	}

    /**
   	 * Delete an article
   	 *
     * @param  int    $articleID   The article id from the database to be deleted
     */
    public static function deleteArticle($articleID) 
    {

    	# Convert the article ID to an integer
  		$articleID = (int)$articleID;

  		# Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

    	# Find the existing article
    	$articleRow = $Query->getArticleById($articleID);

    	# If it doesn't exist return false
    	if (!$articleRow || empty($articleRow)) return false;

    	# Store the id
    	$id = (int)$articleRow['id'];

    	# Remove comments associated with the article
    	$Query->DELETE_FROM('comments')->WHERE('post_id', '=', $id)->QUERY();

    	# Remove the tags associated with the article
    	$Query->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $id)->QUERY();

    	# Remove the content associated with the article
    	$Query->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $id)->QUERY();

    	# Clear the cache
    	self::$Kanso->Cache->clearCache($articleRow['slug']);

    	# Delete the article entry
    	$Query->DELETE_FROM('posts')->WHERE('id', '=', (int)$articleRow['id'])->QUERY();

    	# If the article was a published page, update kanso's static pages
    	if ($articleRow['type'] === 'page' && $articleRow['status'] === 'published') self::removeFromStaticPages($articleRow['slug']);

    	# Fire the article delete event
    	\Kanso\Events::fire('articleDelete', [$articleRow]);

    	return 'valid';

    }

	/**
	 * Clear or Delete a tag or category
	 *
	 * @param  int        $tagID        The tag id to remove
	 * @param  string     $tagType      'category' or 'tag'
	 * @param  boolean    $deleteTag    Should the tag be deleted after clearing it
	 * @return string|boolean
	*/
	public static function clearTag($tagID, $tagType, $deleteTag = false) 
	{

		# Convert the article ID to an integer
  		$tagID = (int)$tagID;

  		# Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

       	# Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

		# Can't or clear delete 'Untagged' or 'Uncategorized'
		if ($tagID === 1) return false;

		# If this is a tag delete. Note tags have a junction table 
		# i.e many posts to 1 tag.
		if ($tagType === 'tag') {

			# Get the tag row
			$tagRow = $Query->SELECT('*')->FROM('tags')->WHERE('id', '=', (int)$tagID)->FIND();
			
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

			return "valid"; 	
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

			return "valid";
		}

		return false;
	}

	/**
	 * Change a tag's slug/and/or name
	 *
	 * @param  int        $tagID        The tag id to remove
	 * @param  string     $tagType      'category' or 'tag'
	 * @param  string     $slug         The tags slug
	 * @param  string     $name         The tags name
	 * @return string|boolean
	*/
	public static function editTag($tagID, $tagType, $slug, $name)
	{

		# Convert the article ID to an integer
  		$tagID = (int)$tagID;

  		# Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

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
    	if ($slugRow) return 'slug_exists';

    	# If there is another tag with the same name - return false;
    	if ($nameRow) return  'name_exists';
 		
    	# Update the tag/category
 		$Query->UPDATE($table)->SET(['name' => $name, 'slug' => $slug])->WHERE('id', '=', (int)$tagRow['id'])->QUERY();

    	return true;

	} 

	/**
	 * Save/Publish/create a new or existing article
	 *
	 * @param   array      $articleInfo        Associative array of the article data
	 * @param   bool       $newArticle         Is this a new article (otional) Defaults to true
	 * @param   bool       $isImport           Is this an import (otional) Defaults to false
	 * @return  bool|array
	 */
	public static function saveArticle($articleInfo, $newArticle = true) 
	{
		# Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

        # If we are saving an existing article, use the article's row
        # instead of the table
		if (!$newArticle) {
			if (!isset($articleInfo['id']) || ($articleInfo['id']) && (int)$articleInfo['id'] < 1) return false;
			$row = $Query->getArticleById((int)$articleInfo['id']);
			
			# If it doesn't exist return false
    		if (!$row) return false;
		}
		else {
			$row = [];
		}

		# Convert the category name to an array of id, name, slug. If the category doesn't exist - create it
		$category = $articleInfo['category'];
		if (\Kanso\Utility\Str::contains($category, ',')) $category = trim(\Kanso\Utility\Str::getBeforeFirstChar($articleInfo['category'], ','));
		if ($category === '') $category = 'Uncategorized';
		$category = self::createCategory($category);

		# Convert the tags to an array of ids. If the tag don't exist - create them
		$tags = $articleInfo['tags'] !== '' ? array_filter(array_map('trim', explode(',', $articleInfo['tags']))) : ['Untagged'];
		$tags  = self::createTags($tags);

		# Validate the content
		$content = $articleInfo['content'];
		if ($content === '')  $content = " ";

		# Validate the title
		$title = trim($articleInfo['title']) === '' ? 'Untitled' : $articleInfo['title'];
		$title = $newArticle ? self::uniqueBaseTitle($title) : $title;

		if ($title === 'Untitled' && !$newArticle) {
			$title 	     = $row['title'];
			$titleExists = $Query->SELECT('title')->FROM('posts')->where('title', '=', $title)->AND_WHERE('id', '!=', (int)$articleInfo['id'])->FIND();
			if ($titleExists) $title = self::uniqueBaseTitle('Untitled');
		}

		# Validate the created and modified times if they were provided
		$created  = !$newArticle ? $row['created'] : null;
		$created  = isset($articleInfo['created']) ? $articleInfo['created'] : $created;
		$created  = !$created ? time() : $created; 
		$modified = !isset($articleInfo['modified']) ? time() : $articleInfo['modified'];		

		# Validate the author if it was provied
		$author   = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
		if (isset($articleInfo['author_id'])) {
			$authorExists = $Query->SELECT('*')->FROM('authors')->WHERE('id', '=', $articleInfo['author_id'])->FIND();
			if ($authorExists)  $author = $authorExists;
		}

		# Validate the status if it was provided
		$status = null;
		if (!$newArticle) $status = $row['status'];
		if (isset($articleInfo['status'])) $status = $articleInfo['status'];
		if (!$status) $status = 'draft';


		# Sanitize the thumbnail
		if (empty($articleInfo['thumbnail'])) $articleInfo['thumbnail'] = '';
		$articleInfo['thumbnail'] = \Kanso\Utility\Str::getAfterLastChar( rtrim($articleInfo['thumbnail'], '/'), '/');

		# Create a slug based on the category, tags, slug, author
		$slug  = self::titleToSlug($title, $category['slug'], $author['slug'], $created, $articleInfo['type']);

		# Insert new post into the database and grab the ID
		$row['status']      = $status;
		$row['type']        = $articleInfo['type'];
		$row['slug']        = $slug;
		$row['title']       = $title;
		$row['excerpt']     = $articleInfo['excerpt'];
		$row['author_id']   = (int)$author['id'];
		$row['category_id'] = (int)$category['id'];
		$row['thumbnail']   = $articleInfo['thumbnail'];
		$row['created']     = $created;
		$row['modified']    = $modified;
		$row['comments_enabled'] = isset($articleInfo['comments']) && $articleInfo['comments'] === 'true' ? true : false;

		$articleExists = isset($row['id']) ? $Query->SELECT('*')->FROM('posts')->WHERE('id', '=', (int)$row['id'])->FIND() : false;

		# If the article does not exist insert a new row
		if (!$articleExists || empty($articleExists)) {
			$Query->INSERT_INTO('posts')->VALUES($row)->QUERY();
			$row['id'] = (int) self::$Kanso->Database->lastInsertId();
		}
		# Otherwise update the existing article and delete the tags
		else {
			$insertRow = \Kanso\Utility\Arr::unsetMultiple(['tags', 'category', 'content', 'comments', 'author'], $row);
			$Query->UPDATE('posts')->SET($insertRow)->WHERE('id', '=', (int)$articleExists['id'])->QUERY();
			$Query->DELETE_FROM('tags_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();
			$Query->DELETE_FROM('content_to_posts')->WHERE('post_id', '=', $row['id'])->QUERY();
		}

		# Join the tags
		foreach ($tags as $tagId) {
			$Query->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $row['id'], 'tag_id' => $tagId])->QUERY();
		}

		# Join the content
		$Query->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $row['id'], 'content' => $content])->QUERY();

		# Fire the event
		\Kanso\Events::fire('newArticle', [$row]);
		
		# If the article is a page, update the static pages list
		if ($row['type'] === 'page') self::addToStaticPages($row['slug']);

		# return the id
		return ['id' => $row['id'], 'slug' => $row['slug']];

	}

	/**
	 * Batch import an array of articles
	 *
	 * @param  array    $articles    Associative array of the articles
	 * @return string
	*/
	public static function batchImport($articles) 
	{


	    # Loop the articles
	  	foreach ($articles as $i => $article) {

	      	# Validate the article's array keys
	  		if (!\Kanso\Utility\Arr::issets(['created', 'modified', 'status', 'type', 'title', 'excerpt', 'category', 'tags', 'content', 'thumbnail', 'comments_enabled'], $article )) return "invalid_json";
	  		if (!is_numeric($article['created'])) return "invalid_json";
	  		if (!is_numeric($article['modified'])) return "invalid_json";
	  		if ($article['type'] !== 'page' && $article['type'] !== 'post') return "invalid_json";
	  		if ($article['status'] !== 'published' && $article['status'] !== 'draft') return "invalid_json";

	      	# Sanitize values
	  		$articles[$i]['title']       = filter_var($articles[$i]['title'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['excerpt']     = filter_var($articles[$i]['excerpt'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['category']    = filter_var($articles[$i]['category'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['thumbnail']   = filter_var($articles[$i]['thumbnail'], FILTER_SANITIZE_STRING);
	  		$articles[$i]['tags']  		 = filter_var($articles[$i]['tags'], FILTER_SANITIZE_STRING);
	  		$article['comments_enabled'] = (bool) $article['comments_enabled'];
	  		$articles[$i]['created']     = (int)$articles[$i]['created'];
	  		$articles[$i]['modified']    = (int)$articles[$i]['modified'];
	  		if (isset($articles[$i]['author_id'])) $articles[$i]['author_id'] = (int)$articles[$i]['author_id'];

	  		if (!self::saveArticle($articles[$i], true, false))  return "invalid_json"; 

	  	}

	  	return 'valid';
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
	private static function titleToSlug($title, $categorySlug, $authorSlug, $created, $type) 
	{
	  	if ($type === 'page') return \Kanso\Utility\Str::slugFilter($title).'/';
	  	$format = self::$Kanso->Config['KANSO_PERMALINKS'];
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
	  		if (isset($dateMap[$key])) $slug .= date($dateMap[$key], $created).'/';
	  		else if (isset($varMap[$key])) $slug .= $varMap[$key].'/';
	  	}
	  	return $slug;

	}

	/**
	 * Create a category if it doesn't exist already
	 *
	 * @param  string   $category       Category name
	 * @return array             
	 */
	private static function createCategory($category) 
	{

		$default = [
	  		'id'   => 1,
	  		'name' => 'Uncategorized',
	  		'slug' => 'uncategorized',
	  	];
	  	
	    # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

        # Check if the category exists
	  	$catRow = $Query->SELECT('*')->FROM('categories')->WHERE('name', '=', $category)->FIND();

	  	# If it exists return it
	  	if ($catRow) return $catRow;

	  	# Otherwise create a new category and return it
	  	$row = [
	  		'name' => $category,
	  		'slug' => \Kanso\Utility\Str::slugFilter($category),
	  	];
	  	$Query->INSERT_INTO('categories')->VALUES($row)->QUERY();
	  	$row['id'] = (int) self::$Kanso->Database->lastInsertId();
	  	return $row;
	}

	/**
	 * Create a tag if it doesn't exist already
	 *
	 * @param  array   $tags       Array of tag names to be created
	 * @return array             
	 */
    private static function createTags($tags) 
    {
    	# Return untagged if nothing was provided
	   	if (empty($tags) || !is_array($tags)) return ['1'];

	    # Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

        # Set an empty list
	   	$tagsList = [];

	   	foreach ($tags as $tag) {
	   		$tagExists  = $Query->SELECT('*')->FROM('tags')->WHERE('name', '=', $tag)->FIND();
	   		if ($tagExists) {
	   			$tagsList[] = (int)$tagExists['id'];
	   		}
	   		else {
	   			$row = [
			  		'name' => $tag,
			  		'slug' => \Kanso\Utility\Str::slugFilter($tag),
			  	];
			  	$Query->INSERT_INTO('tags')->VALUES($row)->QUERY();
			  	$row['id']  = (int)self::$Kanso->Database->lastInsertId();
			  	$tagsList[] = $row['id'];
	   		}
	   	}
   		return $tagsList;
   	}

	/**
	 * Create a title that - append a number to end if it exist already
	 *
	 * @param  string    $path    The input title
	 * @return string             The output title
	 */
	private static function uniqueBaseTitle($title)
	{

		# Set the base title
		$baseTitle = $title;
    	
    	# Counter
    	$i = 1;

    	# Get a new Query Builder
        $Query = self::$Kanso->Database()->Builder();

        # Loop and append number
    	while(!empty($Query->SELECT('*')->FROM('posts')->WHERE('title', '=', $title)->FIND())) {
    		$title = preg_replace("/(".$baseTitle.")(-\d+)/", "$1"."", $title).'-'.$i;
    		$i++;
    	}

    	return $title;
	}

	private static function removeFromStaticPages($slug)
	{
		$staticPages = self::$Kanso->Config()['KANSO_STATIC_PAGES'];
		foreach ($staticPages as $i => $value) {
			if ($value === $slug) unset($staticPages[$i]);
		}
		self::$Kanso->setConfigPair('KANSO_STATIC_PAGES', $staticPages);
		return true;
	}

	private static function addToStaticPages($slug)
	{
		$staticPages = self::$Kanso->Config()['KANSO_STATIC_PAGES'];
		if (in_array($slug, $staticPages)) return true;
		$staticPages[] = $slug;
		self::$Kanso->setConfigPair('KANSO_STATIC_PAGES', $staticPages);
	}

}