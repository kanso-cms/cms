<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * View includes.
 *
 * @author Joe J. Howard
 */
global $KANSO_QUERY;
$KANSO_QUERY = $kanso->Query;

function tag_exists($tag_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->tag_exists($tag_name);
}

function author_exists($author_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->author_exists($author_name);
}

function category_exists($category_name)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->category_exists($category_name);
}

function the_post($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post($post_id);
}

function the_posts()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_posts();
}

function the_title($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_title($post_id);
}

function the_permalink($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_permalink($post_id);
}

function the_slug($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_slug($post_id);
}

function the_excerpt($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_excerpt($post_id);
}

function the_category($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category($post_id);
}

function the_category_name($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_name($post_id);
}

function the_category_url($category_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_url($category_id);
}


function the_category_slug($category_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_slug($category_id);
}

function the_category_id($category_name = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_id($category_name);
}

function the_tags($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tags($post_id);
}

function the_tags_list($post_id = null, $glue = ', ') 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tags_list($post_id, $glue);
}

function the_tag_slug($tag_id) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_slug($tag_id);
}

function the_tag_url($tag_id) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_url($tag_id);
}

function the_taxonomy() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_taxonomy();
}

function the_attachment() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_attachment();
}

function the_attachment_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_attachment_url();
}

function the_attachment_size() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_attachment_size();
}

function the_attachments_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_attachments_url();
}

function the_content($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_content($post_id);
}

function the_post_thumbnail($post_id = null, $size = 'original') 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_thumbnail($post_id, $size);
}

function the_post_thumbnail_src($post_id = null, $size = 'original')
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_thumbnail_src($post_id, $size);
}

function display_thumbnail($thumbnail, $size = 'original', $width = '', $height = '', $classes = '', $id = '')
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->display_thumbnail($thumbnail, $size, $width, $height, $classes, $id);
}

function the_author($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author($post_id);
}

function the_author_name($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_name($post_id);
}

function the_author_url($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_url($author_id);
}

function the_author_thumbnail($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_thumbnail($author_id);
}

function the_author_thumbnail_src($author_id = null, $size = 'small')
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_thumbnail($author_id, $size);
}

function the_author_bio($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_bio($author_id);
}

function the_author_twitter($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_twitter($author_id);
}

function the_author_google($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_google($author_id);
}

function the_author_facebook($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_facebook($author_id);
}

function the_author_instagram($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_instagram($author_id);
}

function the_post_id() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_id();
}

function the_post_status($post_id = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_status($post_id) ;
}

function the_post_type($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_type($post_id);
}

function the_post_meta($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_post_meta($post_id);
}

function the_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_time($format, $post_id);
}

function the_modified_time($format = 'U', $post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_modified_time($format, $post_id);
}

function the_author_posts($author_id, $publihsed = true)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_author_posts($author_id, $publihsed);
}

function the_category_posts($category_id, $publihsed = true)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_category_posts($category_id, $publihsed);
}

function the_tag_posts($tag_id, $publihsed = true)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_tag_posts($tag_id, $publihsed);
}

function the_page_type()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_page_type();
}

function is_single()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_single();
}

function is_custom_post()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_custom_post();
}

function is_home()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_home();
}

function is_front_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_front_page();
}

function is_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_page();
}

function is_search()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_search();
}

function is_tag()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_tag();
}

function is_category()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_category();
}

function is_author()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_author();
}

function is_admin()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_admin();
}

function is_not_found()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_not_found();
}

function has_post_thumbnail($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_post_thumbnail($post_id);
}

function has_author_thumbnail($author_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_author_thumbnail($author_id);
}

function has_tags($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_tags($post_id);
}

function has_category($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_category($post_id);
}

function the_next_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page();
}

function the_previous_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page();
}

function the_next_page_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page_title();
}

function the_previous_page_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page_title();
}

function the_next_page_url()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_next_page_url();
}

function the_previous_page_url()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_previous_page_url();
}


function search_query()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->search_query();
}

function have_posts($post_id = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->have_posts($post_id);
}

function the_posts_count()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_posts_count();
}

function posts_per_page()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->posts_per_page();
}

function blog_location()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->blog_location();
}

function _next()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->_next();
}

function _previous()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->_previous();
}

function all_the_tags()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_tags();
}

function all_the_categories()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_categories();
}

function all_the_authors()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_the_authors();
}

function all_static_pages($publihsed = true) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->all_static_pages($publihsed);
}

function the_header()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_header();
}

function the_footer()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_footer();
}

function the_sidebar()
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->the_sidebar();
}

function include_template($template_name, $data = [])
{
    global $KANSO_QUERY;
    echo $KANSO_QUERY->include_template($template_name, $data);
}

function theme_directory() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->theme_directory();
}

function theme_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->theme_url();
}

function home_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->home_url();
}

function blog_url() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->blog_url();
}

function domain_name() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->domain_name();
}

function website_title() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->website_title();
}

function website_description() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->website_description();
}

function the_meta_title()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_meta_title();
}

function the_canonical_url()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_canonical_url();
}


function the_meta_description()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->the_meta_description();
}


function current_userinfo() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_current_userinfo();
}

function user() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->user();
}

function is_loggedin() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->is_loggedin();
}

function user_is_admin() 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->user_is_admin();
}

function get_gravatar($email_or_md5 = null, $size = 160, $srcOnly = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_gravatar($email_or_md5, $size, $srcOnly);
}

function comments_open($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comments_open($postId);
}

function has_comments($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->has_comments($postId);
}

function comments_number($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comments_number($postId);
}

function comments($postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_comments($postId);
}

function display_comments($args = null, $postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->display_comments($args, $postId);
}

function comment_form($args = null, $postId = null)
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->comment_form($args, $postId);
}

function pagination_links($args = null) 
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->pagination_links($args);
}

function search_form()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->get_search_form();
}

function rewind_posts()
{
    global $KANSO_QUERY;
    return $KANSO_QUERY->rewind_posts();
}