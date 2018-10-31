<?php foreach(all_the_categories() as $category) : if ($category->id === 1) continue; ?>
<url>
	<loc><?php echo the_category_url($category->id); ?></loc>
	<lastmod><?php echo date('Y-m-d\TH:i:sP', time()); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endforeach; ?>
