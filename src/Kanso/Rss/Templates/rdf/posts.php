<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<item rdf:about="<?php echo the_permalink();?>">
		<title><?php echo the_title();?></title>
		<link><?php echo the_permalink();?></link>
		<dc:date><?php echo the_modified_time('D, d M Y H:i:s');?></dc:date>
		<dc:creator><?php echo '<![CDATA['.the_author_name().']]>';?></dc:creator>
		<dc:subject><?php echo '<![CDATA['.the_category_name().']]>';?></dc:subject>
		<description><?php echo '<![CDATA['.the_excerpt().']]>';?></description>
		<content:encoded><?php echo '<![CDATA['.the_content().']]>';?></content:encoded>
	</item>
<?php endwhile; endif; ?>
