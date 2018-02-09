<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?php echo $kanso->Request->environment()->HTTP_HOST;?></loc>
		<lastmod><?php echo date("Y-m-d", time()) . 'T'.date("H:i:sP", time());?></lastmod>
		<changefreq>daily</changefreq>
		<priority>1.0</priority>
	</url>