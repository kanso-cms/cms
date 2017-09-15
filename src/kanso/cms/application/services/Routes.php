<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application routes
 *
 * @author Joe J. Howard
 */

# Defined local variables
$router     = $this->container->Router;
$config     = $this->container->Config;
$SQL        = $this->container->Database->connection()->builder();
$blogPrefix = !empty($config->get('cms.blog_location')) ? '/'.$config->get('cms.blog_location') : '';

# Admin login
$router->get('/admin/login/',  '\kanso\cms\admin\controllers\Accounts@login', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/login/', '\kanso\cms\admin\controllers\Accounts@login', '\kanso\cms\admin\models\Accounts');

# Admin logout
$router->get('/admin/logout/',  '\kanso\cms\admin\controllers\Accounts@logout', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/logout/', '\kanso\cms\admin\controllers\Accounts@logout', '\kanso\cms\admin\models\Accounts');

# Admin forgot pass
$router->get('/admin/forgot-password/',  '\kanso\cms\admin\controllers\Accounts@forgotPassword', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/forgot-password/', '\kanso\cms\admin\controllers\Accounts@forgotPassword', '\kanso\cms\admin\models\Accounts');

# Admin forgot username
$router->get('/admin/forgot-username/',  '\kanso\cms\admin\controllers\Accounts@forgotUsername', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/forgot-username/', '\kanso\cms\admin\controllers\Accounts@forgotUsername', '\kanso\cms\admin\models\Accounts');

# Admin reset password
$router->get('/admin/reset-password/(:all)',  '\kanso\cms\admin\controllers\Accounts@resetPassword', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/reset-password/(:all)', '\kanso\cms\admin\controllers\Accounts@resetPassword', '\kanso\cms\admin\models\Accounts');

# Admin posts
$router->get('/admin/posts/',  	  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->get('/admin/posts/(:all)',  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->post('/admin/posts/',  	  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->post('/admin/posts/(:all)', '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');

# Admin pages
$router->get('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->get('/admin/pages/(:all)',  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->post('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->post('/admin/pages/(:all)', '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');

# Admin tags
$router->get('/admin/tags/',        '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->get('/admin/tags/(:all)',  '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->post('/admin/tags/',  	  '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->post('/admin/tags/(:all)', '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');

# Admin categories
$router->get('/admin/categories/',        '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->get('/admin/categories/(:all)',  '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->post('/admin/categories/',  	    '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->post('/admin/categories/(:all)', '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');

# Admin comments
$router->get('/admin/comments/',          '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->get('/admin/comments/(:all)',    '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->post('/admin/comments/',  	      '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->post('/admin/comments/(:all)',   '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');

# Admin comment authors
$router->get('/admin/comment-users/',         '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\commentUsers');
$router->get('/admin/comment-users/(:all)',   '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\commentUsers');
$router->post('/admin/comment-users/',  	    '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\commentUsers');
$router->post('/admin/comments-users/(:all)', '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\commentUsers');

# Admin settings
$router->get('/admin/settings/',         '\kanso\cms\admin\controllers\Dashboard@settings', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/',        '\kanso\cms\admin\controllers\Dashboard@settings', '\kanso\cms\admin\models\Settings');

# Admin account settings
$router->get('/admin/settings/account/',  '\kanso\cms\admin\controllers\Dashboard@settingsAccount', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/account/', '\kanso\cms\admin\controllers\Dashboard@settingsAccount', '\kanso\cms\admin\models\Settings');

# Admin author settings
$router->get('/admin/settings/author/',  '\kanso\cms\admin\controllers\Dashboard@settingsAuthor', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/author/', '\kanso\cms\admin\controllers\Dashboard@settingsAuthor', '\kanso\cms\admin\models\Settings');

# Admin kanso settings
$router->get('/admin/settings/kanso/',  '\kanso\cms\admin\controllers\Dashboard@settingsKanso', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/kanso/', '\kanso\cms\admin\controllers\Dashboard@settingsKanso', '\kanso\cms\admin\models\Settings');

# Admin access settings
$router->get('/admin/settings/access/',  '\kanso\cms\admin\controllers\Dashboard@settingsAccess', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/access/', '\kanso\cms\admin\controllers\Dashboard@settingsAccess', '\kanso\cms\admin\models\Settings');

# Admin kanso users
$router->get('/admin/settings/users/',  '\kanso\cms\admin\controllers\Dashboard@settingsUsers', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/users/', '\kanso\cms\admin\controllers\Dashboard@settingsUsers', '\kanso\cms\admin\models\Settings');

# Admin kanso tools
$router->get('/admin/settings/tools/',  '\kanso\cms\admin\controllers\Dashboard@settingsTools', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/tools/', '\kanso\cms\admin\controllers\Dashboard@settingsTools', '\kanso\cms\admin\models\Settings');

# Admin writer
$router->get('/admin/writer/',        '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->get('/admin/writer/(:all)',  '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->post('/admin/writer/',       '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->post('/admin/writer/(:any)', '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');

# Admin media
$router->get('/admin/media/',          '\kanso\cms\admin\controllers\Dashboard@mediaLibrary', '\kanso\cms\admin\models\MediaLibrary');
$router->post('/admin/media-library/', '\kanso\cms\admin\controllers\Dashboard@mediaLibrary', '\kanso\cms\admin\models\MediaLibrary');

# Homepage
$router->get('/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/page/(:num)/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/feed/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'home');

# Blog Homepage
if (!empty($blogPrefix))
{
	$router->get("$blogPrefix/", '\kanso\cms\application\Application::applyRoute', 'home');
	$router->get("$blogPrefix/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'home');
	$router->get("$blogPrefix/feed/", '\kanso\cms\application\Application::loadRssFeed', 'home');
	$router->get("$blogPrefix/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'home');
	$router->get("$blogPrefix/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'home');
	$router->get("$blogPrefix/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'home');
}

# Category
if ($config->get('cms.route_categories') === true)
{
	$router->get("$blogPrefix/category/(:any)/", '\kanso\cms\application\Application::applyRoute', 'category');
	$router->get("$blogPrefix/category/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'category');
	$router->get("$blogPrefix/category/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get("$blogPrefix/category/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'category');
}

# Tag
if ($config->get('cms.route_tags') === true)
{
	$router->get("$blogPrefix/tag/(:any)/", '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get("$blogPrefix/tag/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get("$blogPrefix/tag/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'tag');
}

# Author
if ($config->get('cms.route_authors') === true)
{
	$authorSlugs = $SQL->SELECT('slug')->FROM('users')->WHERE('role', '=', 'administrator')->OR_WHERE('role', '=', 'writer')->FIND_ALL();
	
	foreach ($authorSlugs as $slug)
	{
		$slug = $slug['slug'];
		$router->get("$blogPrefix/authors/$slug/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("$blogPrefix/authors/$slug/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("$blogPrefix/authors/$slug/feed/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/authors/$slug/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/authors/$slug/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("$blogPrefix/authors/$slug/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'author');
	}
}

# Static pages
$staticPages = $SQL->SELECT('slug')->FROM('posts')->WHERE('type', '=', 'page')->FIND_ALL();

foreach ($staticPages as $page)
{
	$slug = $page['slug'];
	$router->get("/$slug/", '\kanso\cms\application\Application::applyRoute', 'page');
	$router->get("/$slug/feed/", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rss", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/atom", '\kanso\cms\application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rdf", '\kanso\cms\application\Application::loadRssFeed', 'page');
}

# Search
$router->get('/search-results/(:all)/', '\kanso\cms\application\Application::applyRoute', 'search');
$router->get('/opensearch.xml', '\kanso\Kanso::loadOpenSearch');

# Ajax Post Comments
if ($config->get('cms.enable_comments') === true)
{
	$router->post('/comments/', '\app\controllers\Comments@addComment', '\app\models\Comments');
}

# Sitemap
$router->get('/'.$config->get('cms.sitemap_route'), '\kanso\cms\application\Application::loadSiteMap');

# Attachments
if ($config->get('cms.route_attachments') === true)
{
	$router->get("$blogPrefix/attachment/(:any)/", '\kanso\cms\application\Application::applyRoute', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
	$router->get("$blogPrefix/attachment/(:any)/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'attachment');
}

# Articles
$router->get($blogPrefix.'/'.$config->get('cms.permalinks_route'),              '\kanso\cms\application\Application::applyRoute', 'single');
$router->get($blogPrefix.'/'.$config->get('cms.permalinks_route').'feed/',      '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix.'/'.$config->get('cms.permalinks_route').'feed/rss/',  '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix.'/'.$config->get('cms.permalinks_route').'feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get($blogPrefix.'/'.$config->get('cms.permalinks_route').'feed/rdf/',  '\kanso\cms\application\Application::loadRssFeed', 'single');
