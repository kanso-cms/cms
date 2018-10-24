<?php foreach(all_the_tags() as $tag) : if ($tag->id === 1) continue; ?>
<url>
	<loc><?php echo the_tag_url($tag->id); ?></loc>
	<lastmod><?php echo date('Y-m-d\TH:i:sP', time()); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endforeach; ?>
