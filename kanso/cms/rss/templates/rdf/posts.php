<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<item rdf:about="<?php echo the_permalink(); ?>">
		<title><?php echo htmlspecialchars(the_title()); ?></title>
		<link><?php echo the_permalink(); ?></link>
		<dc:date><?php echo the_modified_time('D, d M Y H:i:s'); ?></dc:date>
		<dc:creator><?php echo '<![CDATA[' . htmlspecialchars(the_author_name()) . ']]>'; ?></dc:creator>
		<dc:subject><?php echo '<![CDATA[' . htmlspecialchars(the_category_name()) . ']]>'; ?></dc:subject>
		<description><?php echo '<![CDATA[' . htmlspecialchars(the_excerpt()) . ']]>'; ?></description>
		<content:encoded><?php echo '<![CDATA[' . htmlspecialchars(the_content()) . ']]>'; ?></content:encoded>
	</item>
<?php endwhile; endif; ?>
