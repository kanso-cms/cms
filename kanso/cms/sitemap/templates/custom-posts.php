<?php foreach(all_custom_posts($type) as $post) : ?>
<url>
	<loc><?php echo the_permalink($post->id); ?></loc>
	<lastmod><?php echo the_modified_time('Y-m-d\TH:i:sP', $post->id); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endforeach; ?>
