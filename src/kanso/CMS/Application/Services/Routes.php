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
$router = $this->container->Router;
$config = $this->container->Config;
$SQL    = $this->container->Database->connection()->builder();

# Admin login
$router->get('/admin/login/',  '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'login');
$router->post('/admin/login/', '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'login');

# Admin logout
$router->get('/admin/logout/',  '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'logout');
$router->post('/admin/logout/', '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'logout');

# Admin forgot pass
$router->get('/admin/forgot-password/',  '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'forgotpassword');
$router->post('/admin/forgot-password/', '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'forgotpassword');

# Admin forgot username
$router->get('/admin/forgot-username/',  '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'forgotusername');
$router->post('/admin/forgot-username/', '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'forgotusername');

# Admin reset password
$router->get('/admin/reset-password/(:all)',  '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'resetpassword');
$router->post('/admin/reset-password/(:all)', '\Kanso\CMS\Admin\Controllers\Accounts@dispatch', 'resetpassword');

# Admin articles
$router->get('/admin/articles/',  	  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'articles');
$router->get('/admin/articles/(:all)',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'articles');
$router->post('/admin/articles/',  	  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'articles');
$router->post('/admin/articles/(:all)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'articles');

# Admin pages
$router->get('/admin/pages/',  	  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'pages');
$router->get('/admin/pages/(:all)',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'pages');
$router->post('/admin/pages/',  	  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'pages');
$router->post('/admin/pages/(:all)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'pages');

# Admin tags
$router->get('/admin/tags/',        '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'tags');
$router->get('/admin/tags/(:all)',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'tags');
$router->post('/admin/tags/',  	  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'tags');
$router->post('/admin/tags/(:all)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'tags');

# Admin categories
$router->get('/admin/categories/',        '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'categories');
$router->get('/admin/categories/(:all)',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'categories');
$router->post('/admin/categories/',  	    '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'categories');
$router->post('/admin/categories/(:all)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'categories');

# Admin comments
$router->get('/admin/comments/',          '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'comments');
$router->get('/admin/comments/(:all)',    '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'comments');
$router->post('/admin/comments/',  	    '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'comments');
$router->post('/admin/comments/(:all)',   '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'comments');

# Admin comment authors
$router->get('/admin/comment-users/',         '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$router->get('/admin/comment-users/(:all)',   '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$router->post('/admin/comment-users/',  	    '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$router->post('/admin/comments-users/(:all)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'commentUsers');

# Admin settings
$router->get('/admin/settings/',         '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settings');
$router->post('/admin/settings/',        '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settings');

# Admin account settings
$router->get('/admin/settings/account/',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsAccount');
$router->post('/admin/settings/account/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsAccount');

# Admin author settings
$router->get('/admin/settings/author/',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsAuthor');
$router->post('/admin/settings/author/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsAuthor');

# Admin kanso settings
$router->get('/admin/settings/kanso/',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsKanso');
$router->post('/admin/settings/kanso/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsKanso');

# Admin kanso users
$router->get('/admin/settings/users/',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsUsers');
$router->post('/admin/settings/users/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsUsers');

# Admin kanso tools
$router->get('/admin/settings/tools/',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsTools');
$router->post('/admin/settings/tools/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'settingsTools');

# Admin writer
$router->get('/admin/writer/',        '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'writer');
$router->get('/admin/writer/(:all)',  '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'writer');
$router->post('/admin/writer/',       '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'writer');
$router->post('/admin/writer/(:any)', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'writer');

# Admin media
$router->get('/admin/media/',          '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'mediaLibrary');
$router->post('/admin/media-library/', '\Kanso\CMS\Admin\Controllers\Dashboard@dispatch', 'mediaLibrary');

# Homepage
$router->get('/', '\Kanso\CMS\Application\Application::applyRoute', 'home');
$router->get('/page/(:num)/', '\Kanso\CMS\Application\Application::applyRoute', 'home');
$router->get('/feed/', '\Kanso\CMS\Application\Application::loadRssFeed', 'home');
$router->get('/feed/rss/', '\Kanso\CMS\Application\Application::loadRssFeed', 'home');
$router->get('/feed/atom/', '\Kanso\CMS\Application\Application::loadRssFeed', 'home');
$router->get('/feed/rdf/', '\Kanso\CMS\Application\Application::loadRssFeed', 'home');

# Category
if ($config->get('cms.route_categories') === true)
{
	$router->get('/category/(:any)/', '\Kanso\CMS\Application\Application::applyRoute', 'category');
	$router->get('/category/(:any)/page/(:num)/', '\Kanso\CMS\Application\Application::applyRoute', 'category');
	$router->get('/category/(:any)/feed/', '\Kanso\CMS\Application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/rss/', '\Kanso\CMS\Application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/atom/', '\Kanso\CMS\Application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/rdf/', '\Kanso\CMS\Application\Application::loadRssFeed', 'category');
}

# Tag
if ($config->get('cms.route_tags') === true)
{
	$router->get('/tag/(:any)/', '\Kanso\CMS\Application\Application::applyRoute', 'tag');
	$router->get('/tag/(:any)/page/(:num)/', '\Kanso\CMS\Application\Application::applyRoute', 'tag');
	$router->get('/tag/(:any)/feed/', '\Kanso\CMS\Application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/rss/', '\Kanso\CMS\Application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/atom/', '\Kanso\CMS\Application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/rdf/', '\Kanso\CMS\Application\Application::loadRssFeed', 'tag');
}

# Author
if ($config->get('cms.route_authors') === true)
{
	$authorSlugs = $SQL->SELECT('slug')->FROM('users')->WHERE('role', '=', 'administrator')->OR_WHERE('role', '=', 'writer')->FIND_ALL();
	
	foreach ($authorSlugs as $slug)
	{
		$slug = $slug['slug'];
		$router->get("/authors/$slug/", '\Kanso\CMS\Application\Application::applyRoute', 'author');
		$router->get("/authors/$slug/page/(:num)/", '\Kanso\CMS\Application\Application::applyRoute', 'author');
		$router->get("/authors/$slug/feed/", '\Kanso\CMS\Application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/rss/", '\Kanso\CMS\Application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/atom/", '\Kanso\CMS\Application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/rdf/", '\Kanso\CMS\Application\Application::loadRssFeed', 'author');
	}
}

# Static pages
$staticPages = $SQL->SELECT('slug')->FROM('posts')->WHERE('type', '=', 'page')->AND_WHERE('status', '=', 'published')->FIND_ALL();

foreach ($staticPages as $page)
{
	$slug = $page['slug'];
	$router->get("/$slug/", '\Kanso\CMS\Application\Application::applyRoute', 'page');
	$router->get("/$slug/feed/", '\Kanso\CMS\Application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rss", '\Kanso\CMS\Application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/atom", '\Kanso\CMS\Application\Application::loadRssFeed', 'page');
	$router->get("/$slug/feed/rdf", '\Kanso\CMS\Application\Application::loadRssFeed', 'page');
}

# Search
$router->get('/search-results/(:all)/', '\Kanso\CMS\Application\Application::applyRoute', 'search');
$router->get('/opensearch.xml', '\Kanso\Kanso::loadOpenSearch');

# Ajax Post Comments
if ($config->get('cms.enable_comments') === true)
{
	$router->post('/comments/', '\Kanso\Comments\Comments@dispatch');
}

# Sitemap
$router->get('/'.$config->get('cms.sitemap_route'), '\Kanso\CMS\Application\Application::loadSiteMap');

# Articles
$router->get('/'.$config->get('cms.permalinks_route'),              '\Kanso\CMS\Application\Application::applyRoute', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/',      '\Kanso\CMS\Application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/rss/',  '\Kanso\CMS\Application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/atom/', '\Kanso\CMS\Application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/rdf/',  '\Kanso\CMS\Application\Application::loadRssFeed', 'single');