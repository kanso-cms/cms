<?php
namespace Kanso\RSS;

/**
 * This class is used to XML/RSS feeds for the homepage and articles
 *
 * When called it will build a valid RSS feed depending on the request
 *
 */
class rssBuilder
{

	/**
	 * Build the sitemap
	 *
	 * @param  array    $pageType    The requested page type
	 * @param  string
	 */
	public static function buildFeed($pageType) 
	{
		# Return a single
        if ($pageType === 'single') return self::buildSingle();

        # Return homepage 
        return self::buildHome();
	}

	private static function buildSingle()
	{
		# Get a Kanso instance
        $Kanso = \Kanso\Kanso::getInstance();

# Return the XML
return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:atom="http://www.w3.org/2005/Atom"
xmlns:sy="http://purl.org/rss/1.0/modules/syndication/">
	<channel>
		<title>'.$Kanso->Query->the_title().'</title>
		<atom:link href="'.$Kanso->Environment['REQUEST_URL'].'" rel="self" type="application/rss+xml" />
		<link>'.$Kanso->Query->the_permalink().'</link>
		<description>'.$Kanso->Query->the_excerpt().'</description>
		<lastBuildDate></lastBuildDate>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<generator>http://kanso-cms.github.io/</generator>
	</channel>
</rss>';
	}

	private static function buildHome()
	{
		# Get a Kanso instance
        $Kanso = \Kanso\Kanso::getInstance();

# Build the XML
$xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
	<channel>
		<title>'.$Kanso->Config['KANSO_SITE_TITLE'].'</title>
		<atom:link href="'.$Kanso->Environment['HTTP_HOST'].'/feed/" rel="self" type="application/rss+xml" />
		<link>'.$Kanso->Environment['HTTP_HOST'].'</link>
		<description>'.$Kanso->Config['KANSO_SITE_DESCRIPTION'].'</description>
		<lastBuildDate>Wed, 30 Dec 2015 05:43:12 +0000</lastBuildDate>
		<language>en-US</language>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<generator>http://kanso-cms.github.io/</generator>';
			# Loop 2 posts
        	$i = 0;
			while ( $Kanso->Query->have_posts() && $i < 2) {
        		$i++;
        		$Kanso->Query->the_post();

# Output the item
$xml .='
		<item>
			<title>'.$Kanso->Query->the_title().'</title>
			<link>'.$Kanso->Query->the_permalink().'</link>
			
			<pubDate>'.$Kanso->Query->the_time('D, d M Y H:i:s').'</pubDate>
			<dc:creator>'.$Kanso->Query->the_author().'</dc:creator>
			<category>'.$Kanso->Query->the_category().'</category>
			<description>'.$Kanso->Query->the_excerpt().'</description>
			<wfw:commentRss>'.$Kanso->Environment['REQUEST_URL'].'</wfw:commentRss>
			<slash:comments>'.$Kanso->Query->comments_number().'</slash:comments>
		</item>';

		}

		#Close the xml
		$xml .='
	</channel>
</rss>';
		
		# Return the xml
		return $xml;
    }

}