<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<url>
	<loc><?php echo the_permalink(); ?></loc>
	<lastmod><?php echo the_modified_time('Y-m-d\TH:i:sP'); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endwhile; endif; ?>
