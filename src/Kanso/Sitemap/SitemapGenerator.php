<?php
namespace Kanso\SiteMap;

/**
 * This class is used to build a valid sitemap for search engine 
 * access and indexing.
 *
 * When called it will build a valid XML sitemap based on databse
 * entries that are provided and save it.
 *
 */
class SitemapGenerator
{

	/**
	 * Build the sitemap
	 *
	 * @param  array    $articles       Articles pulled from the databse
	 * @param  array    $tags    	    Tags pulled from the databse
	 * @param  array    $categories     Categories pulled from the databse
	 * @param  array    $authors        Authors pulled from the databse
	 * @param  string   $websiteBase    Website base url
	 */
	public static function buildSiteMap() {

		# Get a Kanso instance
        $Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query Builder
        $Query = $Kanso->Database()->Builder();

        # Get required data from the database
        $articles    = $Query->SELECT('*')->FROM('posts')->WHERE('status', '=', 'published')->FIND_ALL();
        $tags        = [];
        $categories  = [];
        $authors     = [];
        $websiteBase = $Kanso->Environment['HTTP_HOST'];

        # Only load the tags if tags are being routed
        if ($Kanso->Config['KANSO_ROUTE_TAGS']) {
            $tags = $Query->SELECT('*')->FROM('tags')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the categories if categories are being routed
        if ($Kanso->Config['KANSO_ROUTE_CATEGORIES']) {
            $categories = $Query->SELECT('*')->FROM('categories')->WHERE('id', '!=', 1)->FIND_ALL();
        }
        
        # Only load the authors if authors are being routed
        if ($Kanso->Config['KANSO_ROUTE_AUTHORS']) {
            $authors = $Query->SELECT('*')->FROM('users')->WHERE('status', '=', 'confirmed')->FIND_ALL();
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

		foreach ($articles as $page) {
			$mod    = date("Y-m-d", $page['modified']);
			$mod   .= 'T'.date("H:i:sP", $page['modified']);
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/'.$page["slug"].'</loc>'."\n\t\t";
			$XML .='<lastmod>'.$mod.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.6</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($tags as $tag) {
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/tag/'.$tag['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($categories as $category) {
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/category/'.$category['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		foreach ($authors as $author) {
			$XML .='<url>'."\n\t\t";
			$XML .='<loc>'.$websiteBase.'/author/'.$author['slug'].'/</loc>'."\n\t\t";
			$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
			$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
			$XML .='<priority>0.3</priority>'."\n\t";
			$XML .='</url>'."\n\t";
		}
		$XML .='<url>'."\n\t\t";
		$XML .='<loc>'.$websiteBase.'/search</loc>'."\n\t\t";
		$XML .='<lastmod>'.$now.'</lastmod>'."\n\t\t";
		$XML .='<changefreq>monthly</changefreq>'."\n\t\t";
		$XML .='<priority>0.3</priority>'."\n\t";
		$XML .='</url>'."\n\t";
		$XML .= '</urlset>';

		return $XML;

	}

}