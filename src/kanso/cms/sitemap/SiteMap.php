<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\sitemap;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\database\query\Builder;
use kanso\framework\config\Config;

/**
 * Sitemap builder
 *
 * @author Joe J. Howard
 */
class SiteMap
{
	/**
     * Request object
     *
     * @var \kanso\framework\http\request\Request
     */
	private $request;

	/**
     * Response object
     *
     * @var \kanso\framework\http\response\Response
     */
	private $response;

	/**
     * SQL query builder instance
     * 
     * @var \kanso\framework\database\query\Builder
     */ 
    private $SQL;

    /**
     * Config 
     * 
     * @var \kanso\framework\config\Config
     */
    private $config;

	/**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\http\request\Request   $request  Request object
     * @param  \kanso\framework\http\response\Response $response Response object
     * @param  \kanso\framework\database\query\Builder $SQL      SQL query builder
     * @param  \kanso\framework\config\Config          $config   Framework configuration
     */
    public function __construct(Request $request, Response $response, Builder $SQL, Config $config)
    {
        $this->request = $request;

        $this->response = $response;

        $this->SQL = $SQL;

        $this->config = $config;
    }

	/**
     * Outputs the sitemap to the response
     *
     * @access public
     */
    public function display()
    {
		# Set appropriate content type header
        $this->response->format()->set('xml');

        # Set the response body
        $this->response->body()->set($this->build());
        
        # Set the status
        $this->response->status()->set(200);

        # Disable the cache
        $this->response->cache()->disable();
    }

    /**
	 * Returns the sitemap XML
	 *
	 * @access private
	 * @return string
	 */
	private function build(): string
	{
        # Get required data from the database
        $articles    = $this->SQL->SELECT('*')->FROM('posts')->WHERE('status', '=', 'published')->FIND_ALL();
        $tags        = [];
        $categories  = [];
        $authors     = [];
        $websiteBase = $this->request->environment()->HTTP_HOST;

        # Only load the tags if tags are being routed
        if ($this->config->get('cms.route_tags') === true)
		{
            $tags = $this->SQL->SELECT('*')->FROM('tags')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the categories if categories are being routed
        if ($this->config->get('cms.route_categories') === true)
		{
            $categories = $this->SQL->SELECT('*')->FROM('categories')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the authors if authors are being routed
        if ($this->config->get('cms.route_authors') === true)
		{
            $authors = $this->SQL->SELECT('*')->FROM('users')->WHERE('status', '=', 'confirmed')->FIND_ALL();
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

		foreach ($articles as $page)
		{
			$mod    = date("Y-m-d", $page['modified']);
			$mod   .= 'T'.date("H:i:sP", $page['modified']);
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/'.$page["slug"].'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$mod.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.6</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($tags as $tag)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/tag/'.$tag['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($categories as $category)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/category/'.$category['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($authors as $author)
		{
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/author/'.$author['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
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
