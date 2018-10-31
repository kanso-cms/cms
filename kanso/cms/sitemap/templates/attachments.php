<?php foreach (all_the_attachments() as $attachment) : ?>
<url>
	<loc><?php echo the_attachment_url($attachment->id); ?></loc>
	<lastmod><?php echo date('Y-m-d\TH:i:sP', $attachment->date); ?></lastmod>
	<changefreq>monthly</changefreq>
	<priority>0.6</priority>
</url>
<?php endforeach; ?>