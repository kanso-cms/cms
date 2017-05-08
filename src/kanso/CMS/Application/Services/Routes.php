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
$router->get('/admin/login/',  '\kanso\cms\admin\controllers\Accounts@dispatch', 'login');
$router->post('/admin/login/', '\kanso\cms\admin\controllers\Accounts@dispatch', 'login');

# Admin logout
$router->get('/admin/logout/',  '\kanso\cms\admin\controllers\Accounts@dispatch', 'logout');
$router->post('/admin/logout/', '\kanso\cms\admin\controllers\Accounts@dispatch', 'logout');

# Admin forgot pass
$router->get('/admin/forgot-password/',  '\kanso\cms\admin\controllers\Accounts@dispatch', 'forgotpassword');
$router->post('/admin/forgot-password/', '\kanso\cms\admin\controllers\Accounts@dispatch', 'forgotpassword');

# Admin forgot username
$router->get('/admin/forgot-username/',  '\kanso\cms\admin\controllers\Accounts@dispatch', 'forgotusername');
$router->post('/admin/forgot-username/', '\kanso\cms\admin\controllers\Accounts@dispatch', 'forgotusername');

# Admin reset password
$router->get('/admin/reset-password/(:all)',  '\kanso\cms\admin\controllers\Accounts@dispatch', 'resetpassword');
$router->post('/admin/reset-password/(:all)', '\kanso\cms\admin\controllers\Accounts@dispatch', 'resetpassword');

# Admin articles
$router->get('/admin/articles/',  	  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'articles');
$router->get('/admin/articles/(:all)',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'articles');
$router->post('/admin/articles/',  	  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'articles');
$router->post('/admin/articles/(:all)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'articles');

# Admin pages
$router->get('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'pages');
$router->get('/admin/pages/(:all)',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'pages');
$router->post('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'pages');
$router->post('/admin/pages/(:all)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'pages');

# Admin tags
$router->get('/admin/tags/',        '\kanso\cms\admin\controllers\Dashboard@dispatch', 'tags');
$router->get('/admin/tags/(:all)',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'tags');
$router->post('/admin/tags/',  	  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'tags');
$router->post('/admin/tags/(:all)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'tags');

# Admin categories
$router->get('/admin/categories/',        '\kanso\cms\admin\controllers\Dashboard@dispatch', 'categories');
$router->get('/admin/categories/(:all)',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'categories');
$router->post('/admin/categories/',  	    '\kanso\cms\admin\controllers\Dashboard@dispatch', 'categories');
$router->post('/admin/categories/(:all)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'categories');

# Admin comments
$router->get('/admin/comments/',          '\kanso\cms\admin\controllers\Dashboard@dispatch', 'comments');
$router->get('/admin/comments/(:all)',    '\kanso\cms\admin\controllers\Dashboard@dispatch', 'comments');
$router->post('/admin/comments/',  	    '\kanso\cms\admin\controllers\Dashboard@dispatch', 'comments');
$router->post('/admin/comments/(:all)',   '\kanso\cms\admin\controllers\Dashboard@dispatch', 'comments');

# Admin comment authors
$router->get('/admin/comment-users/',         '\kanso\cms\admin\controllers\Dashboard@dispatch', 'commentUsers');
$router->get('/admin/comment-users/(:all)',   '\kanso\cms\admin\controllers\Dashboard@dispatch', 'commentUsers');
$router->post('/admin/comment-users/',  	    '\kanso\cms\admin\controllers\Dashboard@dispatch', 'commentUsers');
$router->post('/admin/comments-users/(:all)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'commentUsers');

# Admin settings
$router->get('/admin/settings/',         '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settings');
$router->post('/admin/settings/',        '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settings');

# Admin account settings
$router->get('/admin/settings/account/',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsAccount');
$router->post('/admin/settings/account/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsAccount');

# Admin author settings
$router->get('/admin/settings/author/',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsAuthor');
$router->post('/admin/settings/author/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsAuthor');

# Admin kanso settings
$router->get('/admin/settings/kanso/',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsKanso');
$router->post('/admin/settings/kanso/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsKanso');

# Admin kanso users
$router->get('/admin/settings/users/',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsUsers');
$router->post('/admin/settings/users/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsUsers');

# Admin kanso tools
$router->get('/admin/settings/tools/',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsTools');
$router->post('/admin/settings/tools/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'settingsTools');

# Admin writer
$router->get('/admin/writer/',        '\kanso\cms\admin\controllers\Dashboard@dispatch', 'writer');
$router->get('/admin/writer/(:all)',  '\kanso\cms\admin\controllers\Dashboard@dispatch', 'writer');
$router->post('/admin/writer/',       '\kanso\cms\admin\controllers\Dashboard@dispatch', 'writer');
$router->post('/admin/writer/(:any)', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'writer');

# Admin media
$router->get('/admin/media/',          '\kanso\cms\admin\controllers\Dashboard@dispatch', 'mediaLibrary');
$router->post('/admin/media-library/', '\kanso\cms\admin\controllers\Dashboard@dispatch', 'mediaLibrary');

# Homepage
$router->get('/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/page/(:num)/', '\kanso\cms\application\Application::applyRoute', 'home');
$router->get('/feed/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'home');
$router->get('/feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'home');

# Category
if ($config->get('cms.route_categories') === true)
{
	$router->get('/category/(:any)/', '\kanso\cms\application\Application::applyRoute', 'category');
	$router->get('/category/(:any)/page/(:num)/', '\kanso\cms\application\Application::applyRoute', 'category');
	$router->get('/category/(:any)/feed/', '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'category');
	$router->get('/category/(:any)/feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'category');
}

# Tag
if ($config->get('cms.route_tags') === true)
{
	$router->get('/tag/(:any)/', '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get('/tag/(:any)/page/(:num)/', '\kanso\cms\application\Application::applyRoute', 'tag');
	$router->get('/tag/(:any)/feed/', '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/rss/', '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'tag');
	$router->get('/tag/(:any)/feed/rdf/', '\kanso\cms\application\Application::loadRssFeed', 'tag');
}

# Author
if ($config->get('cms.route_authors') === true)
{
	$authorSlugs = $SQL->SELECT('slug')->FROM('users')->WHERE('role', '=', 'administrator')->OR_WHERE('role', '=', 'writer')->FIND_ALL();
	
	foreach ($authorSlugs as $slug)
	{
		$slug = $slug['slug'];
		$router->get("/authors/$slug/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("/authors/$slug/page/(:num)/", '\kanso\cms\application\Application::applyRoute', 'author');
		$router->get("/authors/$slug/feed/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/rss/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/atom/", '\kanso\cms\application\Application::loadRssFeed', 'author');
		$router->get("/authors/$slug/feed/rdf/", '\kanso\cms\application\Application::loadRssFeed', 'author');
	}
}

# Static pages
$staticPages = $SQL->SELECT('slug')->FROM('posts')->WHERE('type', '=', 'page')->AND_WHERE('status', '=', 'published')->FIND_ALL();

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
	$router->post('/comments/', '\kanso\comments\Comments@dispatch');
}

# Sitemap
$router->get('/'.$config->get('cms.sitemap_route'), '\kanso\cms\application\Application::loadSiteMap');

# Articles
$router->get('/'.$config->get('cms.permalinks_route'),              '\kanso\cms\application\Application::applyRoute', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/',      '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/rss/',  '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'single');
$router->get('/'.$config->get('cms.permalinks_route').'feed/rdf/',  '\kanso\cms\application\Application::loadRssFeed', 'single');