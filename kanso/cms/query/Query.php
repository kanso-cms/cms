<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
class Query extends QueryBase
{
    public function the_attachment()
    {
        return $this->helper('attachment')->the_attachment();
    }

    public function all_the_attachments()
    {
        return $this->helper('attachment')->all_the_attachments();
    }

    public function the_attachment_url(int $id = null)
    {
        return $this->helper('attachment')->the_attachment_url($id);
    }

    public function the_attachment_size()
    {
        return $this->helper('attachment')->the_attachment_size();
    }

    public function the_author(int $post_id = null)
    {
        return $this->helper('author')->the_author($post_id);
    }

    public function author_exists($author_name): bool
    {
        return $this->helper('author')->author_exists($author_name);
    }

    public function has_author_thumbnail($author_id = null): bool
    {
        return $this->helper('author')->has_author_thumbnail($author_id);
    }

    public function the_author_name(int $author_id = null)
    {
        return $this->helper('author')->the_author_name($author_id);
    }

    public function the_author_url(int $author_id = null)
    {
        return $this->helper('author')->the_author_url($author_id);
    }

    public function the_author_thumbnail(int $author_id = null)
    {
        return $this->helper('author')->the_author_thumbnail($author_id);
    }

    public function the_author_bio(int $author_id = null)
    {
        return $this->helper('author')->the_author_bio($author_id);
    }

    public function the_author_twitter(int $author_id = null)
    {
        return $this->helper('author')->the_author_twitter($author_id);
    }

    public function the_author_google(int $author_id = null)
    {
        return $this->helper('author')->the_author_google($author_id);
    }

    public function the_author_facebook(int $author_id = null)
    {
        return $this->helper('author')->the_author_facebook($author_id);
    }

    public function the_author_instagram(int $author_id = null)
    {
        return $this->helper('author')->the_author_instagram($author_id);
    }

    public function all_the_authors(): array
    {
        return $this->helper('author')->all_the_authors();
    }

    public function the_author_posts(int $author_id, bool $published = true): array
    {
        return $this->helper('author')->the_author_posts($author_id, $published);
    }

    public function category_exists($category_name): bool
    {
        return $this->helper('category')->category_exists($category_name);
    }

    public function the_category(int $post_id = null)
    {
        return $this->helper('category')->the_category($post_id);
    }

    public function the_category_name(int $post_id = null)
    {
        return $this->helper('category')->the_category_name($post_id);
    }

    public function the_categories(int $post_id = null): array
    {
        return $this->helper('category')->the_categories($post_id);
    }

    public function the_categories_list(int $post_id = null, string $glue = ', '): string
    {
        return $this->helper('category')->the_categories_list($post_id, $glue);
    }

    public function the_category_slug(int $category_id = null)
    {
        return $this->helper('category')->the_category_slug($category_id);
    }

    public function the_category_url(int $category_id = null)
    {
        return $this->helper('category')->the_category_url($category_id);
    }

    public function all_the_categories(): array
    {
        return $this->helper('category')->all_the_categories();
    }

    public function has_categories(int $post_id = null): bool
    {
        return $this->helper('category')->has_categories($post_id);
    }

    public function the_category_posts(int $category_id, bool $published = true): array
    {
        return $this->helper('category')->the_category_posts($category_id, $published);
    }

    public function comments_open(int $post_id = null): bool
    {
        return $this->helper('comment')->comments_open($post_id);
    }

    public function has_comments(int $post_id = null): bool
    {
        return $this->helper('comment')->has_comments($post_id);
    }

    public function comments_number(int $post_id = null): int
    {
        return $this->helper('comment')->comments_number($post_id);
    }

    public function get_comment(int $comment_id)
    {
        return $this->helper('comment')->get_comment($comment_id);
    }

    public function get_comments(int $post_id = null): array
    {
        return $this->helper('comment')->get_comments($post_id);
    }

    public function display_comments(array $args = null, int $post_id = null): string
    {
        return $this->helper('comment')->display_comments($args, $post_id);
    }

    public function comment_form(array $args = null, int $post_id = null): string
    {
        return $this->helper('comment')->comment_form($args, $post_id);
    }

    public function get_gravatar(string $email_or_md5, int $size = 160, bool $srcOnly = false)
    {
        return $this->helper('comment')->get_gravatar($email_or_md5, $size, $srcOnly);
    }

    public function website_title(): string
    {
        return $this->helper('meta')->website_title();
    }

    public function website_description(): string
    {
        return $this->helper('meta')->website_description();
    }

    public function domain_name(): string
    {
        return $this->helper('meta')->domain_name();
    }

    public function the_meta_description(): string
    {
        return $this->helper('meta')->the_meta_description();
    }

    public function the_meta_title(): string
    {
        return $this->helper('meta')->the_meta_title();
    }

    public function the_canonical_url(): string
    {
        return $this->helper('meta')->the_canonical_url();
    }

    public function the_previous_page_title()
    {
        return $this->helper('meta')->the_previous_page_title();
    }

    public function the_next_page_title()
    {
        return $this->helper('meta')->the_next_page_title();
    }

    public function the_next_page_url()
    {
        return $this->helper('meta')->the_next_page_url();
    }

    public function the_previous_page_url()
    {
        return $this->helper('meta')->the_previous_page_url();
    }

    public function the_previous_page()
    {
        return $this->helper('meta')->the_previous_page();
    }

    public function the_next_page()
    {
        return $this->helper('meta')->the_next_page();
    }

    public function pagination_links(array $args = null): string
    {
        return $this->helper('pagination')->pagination_links($args);
    }

    public function the_post_id()
    {
        return $this->helper('post')->the_post_id();
    }

    public function the_excerpt(int $post_id = null)
    {
        return $this->helper('post')->the_excerpt($post_id);
    }

    public function the_post_status(int $post_id = null)
    {
        return $this->helper('post')->the_post_status($post_id);
    }

    public function the_post_type(int $post_id = null)
    {
        return $this->helper('post')->the_post_type($post_id);
    }

    public function the_post_meta($post_id = null): array
    {
        return $this->helper('post')->the_post_meta($post_id);
    }

    public function the_time(string $format = 'U', int $post_id = null)
    {
        return $this->helper('post')->the_time($format, $post_id);
    }

    public function the_modified_time(string $format = 'U', int $post_id = null)
    {
        return $this->helper('post')->the_modified_time($format, $post_id);
    }

    public function has_post_thumbnail(int $post_id = null): bool
    {
        return $this->helper('post')->has_post_thumbnail($post_id);
    }

    public function the_title(int $post_id = null)
    {
        return $this->helper('post')->the_title($post_id);
    }

    public function the_permalink(int $post_id = null)
    {
        return $this->helper('post')->the_permalink($post_id);
    }

    public function the_slug(int $post_id = null)
    {
        return $this->helper('post')->the_slug($post_id);
    }

    public function the_content(int $post_id = null, $raw = false): string
    {
        return $this->helper('post')->the_content($post_id, $raw);
    }

    public function the_post_thumbnail(int $post_id = null)
    {
        return $this->helper('post')->the_post_thumbnail($post_id);
    }

    public function the_post_thumbnail_src(int $post_id = null, string $size = 'original')
    {
        return $this->helper('post')->the_post_thumbnail_src($post_id, $size);
    }

    public function display_thumbnail($thumbnail, $size = 'original', $width = '', $height = '', string $classes = '', string $id = ''): string
    {
        return $this->helper('post')->display_thumbnail($thumbnail, $size, $width, $height, $classes, $id);
    }

    public function all_static_pages(bool $published = true): array
    {
        return $this->helper('post')->all_static_pages($published);
    }

    public function all_custom_posts(string $type, bool $published = true): array
    {
        return $this->helper('post')->all_custom_posts($type, $published);
    }

    public function the_post(int $post_id = null)
    {
        return $this->helper('postIteration')->the_post($post_id);
    }

    public function the_posts(): array
    {
        return $this->helper('postIteration')->the_posts();
    }

    public function the_posts_count(): int
    {
        return $this->helper('postIteration')->the_posts_count();
    }

    public function posts_per_page(): int
    {
        return $this->helper('postIteration')->posts_per_page();
    }

    public function have_posts(int $post_id = null): bool
    {
        return $this->helper('postIteration')->have_posts($post_id);
    }

    public function rewind_posts()
    {
        return $this->helper('postIteration')->rewind_posts();
    }

    public function _next()
    {
        return $this->helper('postIteration')->_next();
    }

    public function _previous()
    {
        return $this->helper('postIteration')->_previous();
    }

    public function search_query()
    {
        return $this->helper('search')->search_query();
    }

    public function get_search_form(): string
    {
        return $this->helper('search')->get_search_form();
    }

    public function tag_exists($tag_name)
    {
        return $this->helper('tag')->tag_exists($tag_name);
    }

    public function the_tags(int $post_id = null)
    {
        return $this->helper('tag')->the_tags($post_id);
    }

    public function the_tags_list(int $post_id = null, string $glue = ', '): string
    {
        return $this->helper('tag')->the_tags_list($post_id, $glue);
    }

    public function the_tag_slug(int $tag_id = null)
    {
        return $this->helper('tag')->the_tag_slug($tag_id);
    }

    public function the_tag_url(int $tag_id = null)
    {
        return $this->helper('tag')->the_tag_url($tag_id);
    }

    public function the_taxonomy()
    {
        return $this->helper('tag')->the_taxonomy();
    }

    public function all_the_tags(): array
    {
        return $this->helper('tag')->all_the_tags();
    }

    public function has_tags(int $post_id = null)
    {
        return $this->helper('tag')->has_tags($post_id);
    }

    public function the_tag_posts(int $tag_id, bool $published = true): array
    {
        return $this->helper('tag')->the_tag_posts($tag_id, $published);
    }

    public function the_header(): string
    {
        return $this->helper('templates')->the_header();
    }

    public function the_footer(): string
    {
        return $this->helper('templates')->the_footer();
    }

    public function the_sidebar(): string
    {
        return $this->helper('templates')->the_sidebar();
    }

    public function include_template(string $template_name, array $data = []): string
    {
        return $this->helper('templates')->include_template($template_name, $data);
    }

    public function themes_directory(): string
    {
        return $this->helper('urls')->themes_directory();
    }

    public function theme_name(): string
    {
        return $this->helper('urls')->theme_name();
    }

    public function theme_directory(): string
    {
        return $this->helper('urls')->theme_directory();
    }

    public function theme_url(): string
    {
        return $this->helper('urls')->theme_url();
    }

    public function home_url(): string
    {
        return $this->helper('urls')->home_url();
    }

    public function blog_url(): string
    {
        return $this->helper('urls')->blog_url();
    }

    public function blog_location()
    {
        return $this->helper('urls')->blog_location();
    }

    public function the_attachments_url(): string
    {
        return $this->helper('urls')->the_attachments_url();
    }

    public function base_url(): string
    {
        return $this->helper('urls')->base_url();
    }

    public function user()
    {
        return $this->helper('validation')->user();
    }

    public function is_loggedIn(): bool
    {
        return $this->helper('validation')->is_loggedIn();
    }

    public function user_is_admin(): bool
    {
        return $this->helper('validation')->user_is_admin();
    }

    public function the_page_type(): string
    {
        return $this->helper('validation')->the_page_type();
    }

    public function is_single(): bool
    {
        return $this->helper('validation')->is_single();
    }

    public function is_custom_post(): bool
    {
        return $this->helper('validation')->is_custom_post();
    }

    public function is_home(): bool
    {
        return $this->helper('validation')->is_home();
    }

    public function is_blog_location(): bool
    {
        return $this->helper('validation')->is_blog_location();
    }

    public function is_front_page(): bool
    {
        return $this->helper('validation')->is_front_page();
    }

    public function is_page($slug = null): bool
    {
        return $this->helper('validation')->is_page($slug);
    }

    public function is_search(): bool
    {
        return $this->helper('validation')->is_search();
    }

    public function is_tag(): bool
    {
        return $this->helper('validation')->is_tag();
    }

    public function is_category(): bool
    {
        return $this->helper('validation')->is_category();
    }

    public function is_author(): bool
    {
        return $this->helper('validation')->is_author();
    }

    public function is_admin(): bool
    {
        return $this->helper('validation')->is_admin();
    }

    public function is_attachment(): bool
    {
        return $this->helper('validation')->is_attachment();
    }

    public function is_not_found(): bool
    {
        return $this->helper('validation')->is_not_found();
    }

    public function fetchPageIndex()
    {
        return $this->helper('filter')->fetchPageIndex();
    }

    public function reset()
    {
        return $this->helper('filter')->reset();
    }

    public function filterPosts(string $requestType)
    {
        return $this->helper('filter')->filterPosts($requestType);
    }

    public function applyQuery(string $queryStr)
    {
        return $this->helper('filter')->applyQuery($queryStr);
    }
}
