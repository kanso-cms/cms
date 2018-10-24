<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<item>
		<title><?php echo the_title(); ?></title>
		<link><?php echo the_permalink(); ?></link>
		<comments><?php echo the_permalink(); ?>#respond</comments>
		<pubDate><?php echo the_modified_time('D, d M Y H:i:s'); ?></pubDate>
		<lastBuildDate><?php echo the_time('D, d M Y H:i:s'); ?></lastBuildDate>
		<dc:creator><?php echo '<![CDATA[' . the_author_name() . ']]>'; ?></dc:creator>
		<category><?php echo '<![CDATA[' . the_category_name() . ']]>'; ?></category>
		<guid><?php echo the_permalink(); ?></guid>
		<description><?php echo '<![CDATA[' . the_excerpt() . ']]>'; ?></description>
		<wfw:commentRss><?php echo the_permalink(); ?>feed/</wfw:commentRss>
		<slash:comments><?php echo comments_number(); ?></slash:comments>
	</item>
<?php endwhile; endif; ?>