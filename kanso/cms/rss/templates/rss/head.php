<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
>
<channel>
	<title><?php echo htmlspecialchars(website_title()); ?></title>
	<atom:link href="<?php echo trim(the_canonical_url(), '/'); ?>/feed/" rel="self" type="application/rss+xml" />
	<link><?php echo the_canonical_url(); ?></link>
	<description><?php echo htmlspecialchars(website_description()); ?></description>
	<lastBuildDate><?php echo the_modified_time('D, d M Y H:i:s'); ?></lastBuildDate>
	<language>en-US</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<generator>http://kanso-cms.github.io/</generator>