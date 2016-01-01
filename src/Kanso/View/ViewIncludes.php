<?php

/**
 * View Includes
 *
 * This set of functions are used directly in the view with templates for local
 * access to Kanso's Query functions without having to access the object directly
 * This file is icluded into all themplates
 *
 * @see \Kanso\View\View
 * @see \Kanso\View\Query
 */

global $KANSO_QUERY;
$KANSO_QUERY = $Kanso->Query();

/**
 * Tag exists
 *
 * @param   string    $tag_name
 * @return  bool  
 */
function tag_exists($tag_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->tag_exists($tag_name);
}

/**
 * Author exists
 *
 * @param   string    $author_name 
 * @return  bool  
 */
function author_exists($author_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->author_exists($author_name);
}

/**
 * Category Exists
 *
 * @param   string    $category_name 
 * @return  bool  
 */
function category_exists($category_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->category_exists($category_name);
}

/**
 * The post
 *
 * Increment the internal pointer by 1 and return the current post 
 * or just return a single post from the database by id
 * @param   int    $post_id (optional) 
 * @return  array|false 
 */
function the_post($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post($post_id);
}

/**
 * Get/Echo the title
 *
 * @param   int    $post_id (optional) 
 * @return  string|false
 */
function the_title($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_title($post_id);
}
function get_the_title($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_title($post_id);
}

/**
 * Get/Echo the permalink
 *
 * @param   int    $post_id (optional) 
 * @return  string|false
 */
function the_permalink($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_permalink($post_id);
}
function get_the_permalink($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_permalink($post_id);
}

/**
 * Get/Echo the slug
 *
 * @param   int    $post_id (optional) 
 * @return  string|false
 */
function the_slug($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_slug($post_id);
}
function get_the_slug($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_slug($post_id);
}

/**
 * Get/Echo the excerpt
 *
 * @param   int    $post_id (optional) 
 * @return  string|false
 */
function the_excerpt($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_excerpt($post_id);
}
function get_the_excerpt($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_excerpt($post_id);
}

/**
 * Get/Echo the category
 *
 * @param   int    $post_id (optional) 
 * @return  string|false
 */
function the_category($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_category($post_id);
}
function get_the_category($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category($post_id);
}

/**
 * Get/Echo category url
 *
 * @param   int    $category_id (optional) 
 * @return  string|false
 */
function the_category_url($category_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_category_url($category_id);
}
function get_the_category_url($category_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_url($category_id);
}

/**
 * Get/Echo the category slug
 *
 * @param   int    $category_id (optional) 
 * @return  string|false
 */
function the_category_slug($category_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_category_slug($category_id);
}
function get_the_category_slug($category_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_slug($category_id);
}

/**
 * Get/Echo the category id
 *
 * @param   string   $category_name (optional) 
 * @return  int|false
 */
function the_category_id($category_name = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_category_id($category_name);
}
function get_the_category_id($category_name = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_id($category_name);
}

/**
 * Get/Echo the tags
 *
 * @param   int   $post_id (optional) 
 * @return  array
 */
function get_the_tags($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tags($post_id);
}

/**
 * Get/Echo the tags as a string
 *
 * @param   int   $post_id (optional) 
 * @return  string
 */
function the_tags_list($post_id = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_tags_list($post_id);
}

function get_the_tags_list($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tags_list($post_id);
}

/**
 * Get/Echo the Tags Slug
 *
 * @param   int   $tag_id 
 * @return  string|false
 */
function the_tag_slug($tag_id) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_tag_slug($tag_id);
}
function get_the_tag_slug($tag_id) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_slug($tag_id);
}

/**
 * Get/Echo the tags URL
 *
 * @param   int   $tag_id 
 * @return  string|false
 */
function the_tag_url($tag_id) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_tag_url($tag_id) ;
}
function get_the_tag_url($tag_id) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_url($tag_id) ;
}

/**
 * Get/Echo the content
 *
 * @param   int   $post_id (optional) 
 * @return  string|false
 */
function the_content($post_id = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_content($post_id);
}
function get_the_content($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_content($post_id);
}

/**
 * Get/Echo the post thumbnail
 *
 * @param   string   $size    (optional) "small/medium/large"
 * @param   int      $post_id (optional)
 * @return  string|false
 */
function the_post_thumbnail($size = 'large', $post_id = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_post_thumbnail($size, $post_id) ;
}
function get_the_post_thumbnail($size = 'large', $post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_thumbnail($size, $post_id) ;
}

/**
 * Get/Echo the author name
 *
 * @param   int      $post_id (optional)
 * @return  string|false
 */
function the_author($post_id = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author($post_id);
}
function get_the_author($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author($post_id);
}

/**
 * Get/Echo the author url 
 *
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_url($author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_url($author_id);
}
function get_the_author_url($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_url($author_id);
}

/**
 * Get/Echo the author thumbnail 
 *
 * @param   string   $size      (optional) "small/medium/large"
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_thumbnail($size = 'small', $author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_thumbnail($size, $author_id);
}
function get_the_author_thumbnail($size = 'small', $author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_thumbnail($size, $author_id);
}

/**
 * Get/Echo the author bio 
 *
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_bio($author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_bio($author_id);
}
function get_the_author_bio($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_bio($author_id);
}

/**
 * Get/Echo the author twitter 
 *
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_twitter($author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_twitter($author_id);
}
function get_the_author_twitter($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_twitter($author_id);
}

/**
 * Get/Echo the author google 
 *
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_google($author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_google($author_id);
}
function get_the_author_google($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_google($author_id);
}

/**
 * Get/Echo the author facebook 
 *
 * @param   int      $author_id (optional)
 * @return  string|false
 */
function the_author_facebook($author_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_author_facebook($author_id);
}
function get_the_author_facebook($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_facebook($author_id);
}

/**
 * Get/Echo the post ID 
 *
 * @return  int|false
 */
function the_post_id() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_post_id();
}
function get_the_post_id() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_id();
}

/**
 * Get/Echo the post status 
 *
 * @param   int      $post_id (optional)
 * @return  string|false
 */
function the_post_status($post_id = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_post_status($post_id) ;
}
function get_the_post_status($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_status($post_id) ;
}

/**
 * Get/Echo the post type 
 *
 * @param   int      $post_id (optional)
 * @return  string|false
 */
function the_post_type($post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_post_type($post_id);
}
function get_the_post_type($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_type($post_id);
}

/**
 * Get/Echo the post created time 
 *
 * @param   string   $format  (optional)
 * @param   int      $post_id (optional)
 * @return  string|int|false
 */
function the_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_time($format, $post_id);
}
function get_the_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_time($format, $post_id);
}

/**
 * Get/Echo the post modified time 
 *
 * @param   string   $format  (optional)
 * @param   int      $post_id (optional)
 * @return  string|int|false
 */
function the_modified_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_modified_time($format, $post_id);
}
function get_the_modified_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_modified_time($format, $post_id);
}

/**
 * Get the author's posts 
 *
 * @param   int      $author_id
 * @return  array
 */
function get_the_author_posts($author_id)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_posts($author_id);
}

/**
 * Get the category's posts 
 *
 * @param   int      $category_id
 * @return  array
 */
function get_the_category_posts($category_id)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_posts($category_id);
}

/**
 * Get the tag's posts 
 *
 * @param   int      $tag_id
 * @return  array
 */
function get_the_tag_posts($tag_id)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_posts($tag_id);
}

/**
 * Get/Echo the page type 
 *
 * @return  string
 */
function the_page_type()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_page_type();
}
function get_the_page_type()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_page_type();
}

/**
 * Is single
 *
 * @return  bool
 */
function is_single()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_single();
}

/**
 * Is home
 *
 * @return  bool
 */
function is_home()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_home();
}

/**
 * Is front page
 *
 * @return  bool
 */
function is_front_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_front_page();
}

/**
 * Is page
 *
 * @return  bool
 */
function is_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_page();
}

/**
 * Is archive
 *
 * @return  bool
 */
function is_archive()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_archive();
}

/**
 * Is search
 *
 * @return  bool
 */
function is_search()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_search();
}

/**
 * Is tag
 *
 * @return  bool
 */
function is_tag()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_tag();
}

/**
 * Is category
 *
 * @return  bool
 */
function is_category()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_category();
}

/**
 * Is author
 *
 * @return  bool
 */
function is_author()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_author();
}

/**
 * Is admin
 *
 * @return  bool
 */
function is_admin()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_admin();
}

/**
 * Has post Thumbnail
 *
 * @param   int   $post_id   (optional)
 * @return  bool
 */
function has_post_thumbnail($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_post_thumbnail($post_id);
}

/**
 * Has author Thumbnail
 *
 * @param   int   $post_id   (optional)
 * @return  bool
 */
function has_author_thumbnail($author_id)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_author_thumbnail($author_id);
}

/**
 * Has excerpt
 *
 * @param   int   $post_id   (optional)
 * @return  bool
 */
function has_excerpt($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_excerpt($post_id);
}

/**
 * Has tags
 *
 * @param   int   $post_id   (optional)
 * @return  bool
 */
function has_tags($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_tags($post_id);
}

/**
 * Has category
 *
 * @param   int   $post_id   (optional)
 * @return  bool
 */
function has_category($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_category($post_id);
}

/**
 * Get/Echo the page title
 *
 * @return  string
 */
function the_page_title()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_page_title();
}
function get_the_page_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_page_title();
}

/**
 * Get the next page 
 *
 * @return  array|false   (array of slug/title)
 */
function get_the_next_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page();
}

/**
 * Get the previous page
 *
 * @return  array|false (array of slug/title)
 */
function get_the_previous_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page();
}

/**
 * Get/Echo the next page title
 *
 * @return  string|false
 */
function the_next_page_title()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_next_page_title();
}
function get_the_next_page_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page_title();
}

/**
 * Echo the previous page title
 *
 * @return  string|false
 */
function the_previous_page_title()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_previous_page_title();
}
function get_the_previous_page_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page_title();
}

/**
 * Get/Echo the next page url
 *
 * @return  string|false
 */
function the_next_page_url()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_next_page_url();
}
function get_the_next_page_url()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page_url();
}

/**
 * Get/Echo the previous page url
 *
 * @return  string|false
 */
function the_previous_page_url()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_previous_page_url();
}
function get_the_previous_page_url()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page_url();
}

/**
 * Get/Echo the search query
 *
 * @return  string|false
 */

function search_query()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->search_query();
}
function get_search_query()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->search_query();
}

/**
 * Do we have posts? 
 *
 * @param   int  $post_id (optional)
 * @return  bool
 */
function have_posts($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->have_posts($post_id);
}

/**
 * How many posts are there
 *
 * @param   int  $post_id (optional)
 * @return  bool
 */
function the_posts_count()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_posts_count();
}

/**
 * How many posts per page are displayed
 *
 * @param   int  $post_id (optional)
 * @return  bool
 */
function posts_per_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->posts_per_page();
}

/**
 * Next post
 */
function _next()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->_next();
}

/**
 * Previous post
 */
function previous()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->previous();
}

/**
 * Next page
 */
function next_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->next_page();
}

/**
 * Previous page
 */
function previus_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->previus_page();
}

/**
 * All the tags 
 * @return array
 */
function all_the_tags()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_tags();
}

/**
 * All the categories 
 * @return array
 */
function all_the_categories()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_categories();
}

/**
 * All the authors 
 * @return array
 */
function all_the_authors()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_authors();
}

/**
 * All the static pages 
 * @return array
 */
function all_static_pages() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->static_pages();
}

/**
 * The header
 * @return string
 */
function the_header()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_header();
}

/**
 * The footer
 * @return string
 */
function the_footer()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_footer();
}

/**
 * The sidebar
 * @return string
 */
function the_sidebar()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_sidebar();
}

/**
 * Include
 * @return string
 */
function include_template($template_name)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->include_template($template_name);
}

/**
 * Get/echo the theme directory
 * @return string
 */
function theme_directory() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->theme_directory();
}
function get_theme_directory() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->theme_directory();
}

/**
 * Get/echo the theme url
 * @return string
 */
function theme_url() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->theme_url();
}
function get_theme_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->theme_url();
}

/**
 * Get/echo the homepage
 * @return string
 */
function home_url() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->home_url();
}
function get_home_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->home_url();
}

/**
 * Get/echo the website name 
 * eg http://www.example.com returns example.com
 * @return string
 */
function website_name() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->website_name();
}
function get_website_name() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->website_name();
}

/**
 * Get/echo the website title 
 * @return string
 */
function website_title() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->website_title();
}
function get_website_title() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->website_title();
}

/**
 * Get/echo the website description 
 * @return string
 */
function website_description() 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->website_description();
}
function get_website_description() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->website_description();
}

/**
 * Get current Kanso logged in user info
 * @return array
 */
function get_current_userinfo() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_current_userinfo();
}

/**
 * Validate that the current user is logged in to Kanso's admin panel
 * @return bool
 */
function is_loggedin() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_loggedin();
}

/**
 * Get a user's avatar 'img tag'
 * @return bool
 */
function get_avatar($email_or_md5 = null, $size = 160, $srcOnly = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_avatar($email_or_md5, $size, $srcOnly);
}

/**
 * Are comments open on a given article
 * @return bool
 */
function comments_open($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comments_open($postId);
}

/**
 * Does an article have any comments
 * @return bool
 */
function has_comments($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_comments($postId);
}

/**
 * Get/echo the total comments number
 * @return bool
 */
function comments_number($postId = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->comments_number($postId);
}
function get_comments_number($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comments_number($postId);
}

/**
 * Get an article's comments as an associative array
 * @return array
 */
function get_comments($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_comments($postId);
}

/**
 * Get/echo an article's comments as HTML
 * @return string
 */
function display_comments($args = null, $postId = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->display_comments($args, $postId);
}
function get_display_comments($args = null, $postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->display_comments($args, $postId);
}

/**
 * Get/echo a comment form
 * @return string
 */
function comment_form($args = null, $postId = null)
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->comment_form($args, $postId);
}
function get_comment_form($args = null, $postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comment_form($args, $postId);
}

/**
 * Build HTML Pagination links
 *
 * @param  array       $args    Associative array of options (optional)
 */
function pagination_links($args = null) 
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->pagination_links($args);
}

/**
 * Get theme search form
 *
 * @param  string
 */
function get_search_form()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->get_search_form();
}

/**
 * Get posts archived by year, month
 *
 * @return  array
 */
function get_archives()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_archives();
}