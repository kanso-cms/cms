<?php foreach(all_the_authors() as $author) : ?>
<url>
	<loc><?php echo the_author_url($author->id); ?></loc>
	<lastmod><?php echo date('Y-m-d\TH:i:sP', time()); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endforeach; ?>
