<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\sitemap;

use kanso\framework\mvc\model\Model;

/**
 * Sitemap builder
 *
 * @author Joe J. Howard
 */
class SiteMap extends model
{
	
	/**
     * Outputs the sitemap to the response
     *
     * @access public
     */
    public function display()
    {
		# Set appropriate content type header
        $this->Response->format()->set('xml');

        # Set the response body
        $this->Response->body()->set($this->build());
        
        # Set the status
        $this->Response->status()->set(200);

        # Disable the cache
        $this->Response->cache()->disable();
    }

    /**
	 * Returns the sitemap XML
	 *
	 * @access private
	 * @return string
	 */
	private function build(): string
	{
		# Save SQL builder locally
        $SQL = $this->Database->connection()->builder();

        # Get required data from the database
        $posts           = $SQL->SELECT('*')->FROM('posts')->WHERE('status', '=', 'published')->AND_WHERE('type', '=', 'post')->FIND_ALL();
        $staticPages     = $SQL->SELECT('*')->FROM('posts')->WHERE('status', '=', 'published')->AND_WHERE('type', '=', 'page')->FIND_ALL();
        $customPostTypes = $this->Config->get('cms.custom_posts');

        $customPosts = [];
        $tags        = [];
        $categories  = [];
        $authors     = [];
        $websiteBase = $this->Request->environment()->HTTP_HOST;

        # Only load the tags if tags are being routed
        if ($this->Config->get('cms.route_tags') === true)
		{
            $tags = $SQL->SELECT('*')->FROM('tags')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the categories if categories are being routed
        if ($this->Config->get('cms.route_categories') === true)
		{
            $categories = $SQL->SELECT('*')->FROM('categories')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the authors if authors are being routed
        if ($this->Config->get('cms.route_authors') === true)
		{
            $authors = $SQL->SELECT('*')->FROM('users')->WHERE('status', '=', 'confirmed')->FIND_ALL();
        }

        # Only load custom posts if they exist
        if (!empty($customPostTypes))
        {
        	foreach ($customPostTypes as $type => $route)
        	{
        		$cPosts      = $SQL->SELECT('*')->FROM('posts')->WHERE('status', '=', 'published')->AND_WHERE('type', '=', $type)->FIND_ALL();
        		$customPosts = array_merge($customPosts, $cPosts);
        	}
        }

		$now    = date("Y-m-d", time());
		$now   .= 'T'.date("H:i:sP", time());
		$XML    = "";

		$XML .='<?xml version="1.0" encoding="UTF-8"?>'."\n\t";
		$XML .='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n\t\t";
		$XML .='<url>'."\n\t\t";
		$XML .='<loc>'.$websiteBase.'</loc>'."\n\t\t\t";
		$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t\t";
		$XML .='<changefreq>daily</changefreq>'."\n\t\t\t";
		$XML .='<priority>1.0</priority>'."\n\t\t";
		$XML .='</url>'."\n\t";

		foreach ($staticPages as $page)
		{
			$mod  = date("Y-m-d", $page['modified']);
			$mod .= 'T'.date("H:i:sP", $page['modified']);
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_permalink($page['id']).'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$mod.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.6</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($tags as $tag)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_tag_url($tag['id']).'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($categories as $category)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_category_url($category['id']).'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($authors as $author)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_author_url($author['id']).'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($posts as $post)
		{
			$mod  = date("Y-m-d", $post['modified']);
			$mod .= 'T'.date("H:i:sP", $post['modified']);
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_permalink($post['id']).'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$mod.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.6</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($customPosts as $post)
		{
			$mod  = date("Y-m-d", $post['modified']);
			$mod .= 'T'.date("H:i:sP", $post['modified']);
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$this->Query->the_permalink($post['id']).'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$mod.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.6</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}

		$XML .='<url>'."\n\t\t";
		$XML .='<loc>'.$websiteBase.'/search-results/</loc>'."\n\t\t";
		$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
		$XML .='<changefreq>always</changefreq>'."\n\t\t";
		$XML .='<priority>0.3</priority>'."\n\t";
		$XML .='</url>'."\n\t";

		$XML .= '</urlset>';

		return $XML;
	}
}
