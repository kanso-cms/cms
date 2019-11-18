<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\query;

use kanso\tests\TestCase;

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

	/**
	 *
	 */
	public function testMethodsExist(): void
	{
		require_once KANSO_DIR . '/cms/query/Includes.php';

		foreach ($this->queryMethods as $func)
		{
			$this->assertTrue(function_exists($func));
		}
	}
}
