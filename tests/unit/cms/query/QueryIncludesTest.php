<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\query;

use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class QueryIncludesTest extends TestCase
{
	private $queryMethods =
	[
		'the_attachment',
		'all_the_attachments',
		'the_attachment_url',
		'the_attachment_size',

		'the_author',
		'author_exists',
		'has_author_thumbnail',
		'the_author_name',
		'the_author_url',
		'the_author_thumbnail',
		'the_author_bio',
		'the_author_twitter',
		'the_author_google',
		'the_author_facebook',
		'the_author_instagram',
		'all_the_authors',
		'the_author_posts',

		'category_exists',
		'the_category',
		'the_category_name',
		'the_categories',
		'the_categories_list',
		'the_category_slug',
		'the_category_url',
		'all_the_categories',
		'has_categories',
		'the_category_posts',

		'comments_open',
		'has_comments',
		'comments_number',
		'comment',
		'comments',
		'display_comments',
		'comment_form',
		'get_gravatar',

		'website_title',
		'website_description',
		'domain_name',
		'the_meta_description',
		'the_meta_title',
		'the_canonical_url',
		'the_previous_page_title',
		'the_next_page_title',
		'the_next_page_url',
		'the_previous_page_url',
		'the_previous_page',
		'the_next_page',

		'pagination_links',

		'the_post_id',
		'the_excerpt',
		'the_post_status',
		'the_post_type',
		'the_post_meta',
		'the_time',
		'the_modified_time',
		'has_post_thumbnail',
		'the_title',
		'the_permalink',
		'the_slug',
		'the_content',
		'the_post_thumbnail',
		'the_post_thumbnail_src',
		'display_thumbnail',
		'all_static_pages',
		'all_custom_posts',

		'the_post',
		'the_posts',
		'the_posts_count',
		'posts_per_page',
		'have_posts',
		'rewind_posts',
		'_next',
		'_previous',

		'search_query',
 		'search_form',

 		'tag_exists',
		'the_tags',
		'the_tags_list',
		'the_tag_slug',
		'the_tag_url',
		'the_taxonomy',
		'all_the_tags',
		'has_tags',
		'the_tag_posts',

		'the_header',
		'the_footer',
		'the_sidebar',
		'include_template',

		'themes_directory',
		'theme_name',
		'theme_directory',
		'theme_url',
		'home_url',
		'blog_url',
		'blog_location',
		'the_attachments_url',
		'base_url',

		'user',
		'is_loggedIn',
		'user_is_admin',
		'the_page_type',
		'is_single',
		'is_custom_post',
		'is_home',
		'is_blog_location',
		'is_page',
		'is_search',
		'is_tag',
		'is_category',
		'is_author',
		'is_admin',
		'is_attachment',
		'is_not_found',
	];

	private $queryCalls =
	[
		'the_attachment' => [null, 'the_attachment'],
		'all_the_attachments' => [null, 'all_the_attachments'],
		'the_attachment_url' => [null, 'the_attachment_url'],
		'the_attachment_size' => [null, 'the_attachment_size'],

		'the_author' => [null, 'the_author'],
		'author_exists' => [null, 'author_exists'],
		'has_author_thumbnail' => [null, 'has_author_thumbnail'],
		'the_author_name' => [null, 'the_author_name'],
		'the_author_url' => [null, 'the_author_url'],
		'the_author_thumbnail' => [null, 'the_author_thumbnail'],
		'the_author_bio' => [null, 'the_author_bio'],
		'the_author_twitter' => [null, 'the_author_twitter'],
		'the_author_google' => [null, 'the_author_google'],
		'the_author_facebook' => [null, 'the_author_facebook'],
		'the_author_instagram' => [null, 'the_author_instagram'],
		'all_the_authors' => [null, 'all_the_authors'],
		'the_author_posts' => [1, 'the_author_posts'],

		'category_exists' => [1, 'category_exists'],
		'the_category' => [null, 'the_category'],
		'the_category_name' => [null, 'the_category_name'],
		'the_categories' => [null, 'the_categories'],
		'the_categories_list' => [null, 'the_categories_list'],
		'the_category_slug' => [null, 'the_category_slug'],
		'the_category_url' => [null, 'the_category_url'],
		'all_the_categories' => [null, 'all_the_categories'],
		'has_categories' => [1, 'has_categories'],
		'the_category_posts' => [1, 'the_category_posts'],

		'comments_open' => [null, 'comments_open'],
		'has_comments' => [null, 'has_comments'],
		'comments_number' => [null, 'comments_number'],
		'comment' => [1, 'get_comment'],
		'comments' => [null, 'get_comments'],
		'display_comments' => [null, 'display_comments'],
		'comment_form' => [null, 'comment_form'],
		'get_gravatar' => ['foo@bar.com', 'get_gravatar'],

		'website_title' => [null, 'website_title'],
		'website_description' => [null, 'website_description'],
		'domain_name' => [null, 'domain_name'],
		'the_meta_description' => [null, 'the_meta_description'],
		'the_meta_title' => [null, 'the_meta_title'],
		'the_canonical_url' => [null, 'the_canonical_url'],
		'the_previous_page_title' => [null, 'the_previous_page_title'],
		'the_next_page_title' => [null, 'the_next_page_title'],
		'the_next_page_url' => [null, 'the_next_page_url'],
		'the_previous_page_url' => [null, 'the_previous_page_url'],
		'the_previous_page' => [null, 'the_previous_page'],
		'the_next_page' => [null, 'the_next_page'],

		'pagination_links' => [null, 'pagination_links'],

		'the_post_id' => [null, 'the_post_id'],
		'the_excerpt' => [null, 'the_excerpt'],
		'the_post_status' => [null, 'the_post_status'],
		'the_post_type' => [null, 'the_post_type'],
		'the_post_meta' => [null, 'the_post_meta'],
		'the_time' => ['y-m-d', 'the_time'],
		'the_modified_time' => ['y-m-d', 'the_modified_time'],
		'has_post_thumbnail' => [null, 'has_post_thumbnail'],
		'the_title' => [null, 'the_title'],
		'the_permalink' => [null, 'the_permalink'],
		'the_slug' => [null, 'the_slug'],
		'the_content' => [null, 'the_content'],
		'the_post_thumbnail' => [null, 'the_post_thumbnail'],
		'the_post_thumbnail_src' => [null, 'the_post_thumbnail_src'],
		'display_thumbnail' => [null, 'display_thumbnail'],
		'all_static_pages' => [false, 'all_static_pages'],
		'all_custom_posts' => ['type', 'all_custom_posts'],

		'the_post' => [null, 'the_post'],
		'the_posts' => [null, 'the_posts'],
		'the_posts_count' => [null, 'the_posts_count'],
		'posts_per_page' => [null, 'posts_per_page'],
		'have_posts' => [null, 'have_posts'],
		'rewind_posts' => [null, 'rewind_posts'],
		'_next' => [null, '_next'],
		'_previous' => [null, '_previous'],

		'search_query' => [null, 'search_query'],
		'search_form' => [null, 'get_search_form'],

		'tag_exists' => [1, 'tag_exists'],
		'the_tags' => [null, 'the_tags'],
		'the_tags_list' => [null, 'the_tags_list'],
		'the_tag_slug' => [null, 'the_tag_slug'],
		'the_tag_url' => [null, 'the_tag_url'],
		'the_taxonomy' => [null, 'the_taxonomy'],
		'all_the_tags' => [null, 'all_the_tags'],
		'has_tags' => [null, 'has_tags'],
		'the_tag_posts' => [1, 'the_tag_posts'],

		'the_header' => [null, 'the_header'],
		'the_footer' => [null, 'the_footer'],
		'the_sidebar' => [null, 'the_sidebar'],
		'include_template' => ['foo.php', 'include_template'],

		'themes_directory' => [null, 'themes_directory'],
		'theme_name' => [null, 'theme_name'],
		'theme_directory' => [null, 'theme_directory'],
		'theme_url' => [null, 'theme_url'],
		'home_url' => [null, 'home_url'],
		'blog_url' => [null, 'blog_url'],
		'blog_location' => [null, 'blog_location'],
		'the_attachments_url' => [null, 'the_attachments_url'],
		'base_url' => [null, 'base_url'],

		'user' => [null, 'user'],
		'is_loggedIn' => [null, 'is_loggedIn'],
		'user_is_admin' => [null, 'user_is_admin'],
		'the_page_type' => [null, 'the_page_type'],
		'is_single' => [null, 'is_single'],
		'is_custom_post' => [null, 'is_custom_post'],
		'is_home' => [null, 'is_home'],
		'is_blog_location' => [null, 'is_blog_location'],
		'is_page' => [null, 'is_page'],
		'is_search' => [null, 'is_search'],
		'is_tag' => [null, 'is_tag'],
		'is_category' => [null, 'is_category'],
		'is_author' => [null, 'is_author'],
		'is_admin' => [null, 'is_admin'],
		'is_attachment' => [null, 'is_attachment'],
		'is_not_found' => [null, 'is_not_found'],
	];

	/**
	 *
	 */
	public function testMethodsExist()
	{
		$kanso = Mockery::mock('\Kanso\kanso');

		$query = Mockery::mock('\kanso\cms\query\Query');

		$kanso->Query = $query;

		require_once KANSO_DIR . '/cms/query/Includes.php';

		foreach ($this->queryMethods as $func)
		{
			$this->assertTrue(function_exists($func));
		}

		unset($KANSO_QUERY);
    	$KANSO_QUERY = null;
	}

	/**
	 *
	 */
	public function testMethodCallsQuery()
	{
		$kanso = Mockery::mock('\Kanso\kanso');

		$query = Mockery::mock('\kanso\cms\query\Query');

		$kanso->Query = $query;

		require_once KANSO_DIR . '/cms/query/Includes.php';

		global $KANSO_QUERY;
		$KANSO_QUERY = $query;

		foreach ($this->queryCalls as $func => $queryFunc)
		{
			$args   = $queryFunc[0];
			$method = $queryFunc[1];

			$query->shouldReceive($method)->once();

			$func($args);
		}

		unset($KANSO_QUERY);

    	$KANSO_QUERY = null;
	}
}
