<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?><rdf:RDF
	xmlns="http://purl.org/rss/1.0/"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:admin="http://webns.net/mvcb/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	>
	<channel rdf:about="<?php echo home_url(); ?>">
		<title><?php echo website_title(); ?></title>
		<link><?php echo home_url(); ?></link>
		<description><?php echo website_description();?></description>
		<dc:date><?php echo the_modified_time('c');?></dc:date>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
		<admin:generatorAgent rdf:resource="http://kanso-cms.github.io/" />
		<items>
			<rdf:Seq>
				<?php while (have_posts()) : the_post(); ?>
					<rdf:li rdf:resource="<?php echo the_permalink();?>"/>
				<?php endwhile; rewind_posts(); ?>
			</rdf:Seq>
		</items>
	</channel>
	




