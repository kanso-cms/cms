<?php

# Router should not halt on match incase the permalinks
# route is the same as the category or tags listings

# e.g example.com/category/cat-slug -> example.com/cat-name/post-name
$this->Router->haltOnMatch(false);

# 404 All Kanso/ .php requests
$this->get('/Kanso/(:all).php', [$this, 'notFound']);

# Admin login
$this->get('/admin/login/',  '\Kanso\Admin\Controllers\Account@dispatch', 'login');
$this->post('/admin/login/', '\Kanso\Admin\Controllers\Account@dispatch', 'login');

# Admin logout
$this->get('/admin/logout/',  '\Kanso\Admin\Controllers\Account@dispatch', 'logout');
$this->post('/admin/logout/', '\Kanso\Admin\Controllers\Account@dispatch', 'logout');

# Admin forgot pass
$this->get('/admin/forgot-password/',  '\Kanso\Admin\Controllers\Account@dispatch', 'forgotpassword');
$this->post('/admin/forgot-password/', '\Kanso\Admin\Controllers\Account@dispatch', 'forgotpassword');

# Admin forgot username
$this->get('/admin/forgot-username/',  '\Kanso\Admin\Controllers\Account@dispatch', 'forgotusername');
$this->post('/admin/forgot-username/', '\Kanso\Admin\Controllers\Account@dispatch', 'forgotusername');

# Admin reset password
$this->get('/admin/reset-password/(:all)',  '\Kanso\Admin\Controllers\Account@dispatch', 'resetpassword');
$this->post('/admin/reset-password/(:all)', '\Kanso\Admin\Controllers\Account@dispatch', 'resetpassword');

# Admin register
$this->get('/admin/register/(:all)',  '\Kanso\Admin\Controllers\Account@dispatch', 'register');
$this->post('/admin/register/(:all)',  '\Kanso\Admin\Controllers\Account@dispatch', 'register');

# Admin articles
$this->get('/admin/articles/',  	  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'articles');
$this->get('/admin/articles/(:all)',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'articles');
$this->post('/admin/articles/',  	  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'articles');
$this->post('/admin/articles/(:all)', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'articles');

# Admin pages
$this->get('/admin/pages/',  	  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'pages');
$this->get('/admin/pages/(:all)',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'pages');
$this->post('/admin/pages/',  	  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'pages');
$this->post('/admin/pages/(:all)', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'pages');

# Admin tags
$this->get('/admin/tags/',        '\Kanso\Admin\Controllers\Dashboard@dispatch', 'tags');
$this->get('/admin/tags/(:all)',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'tags');
$this->post('/admin/tags/',  	  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'tags');
$this->post('/admin/tags/(:all)', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'tags');

# Admin categories
$this->get('/admin/categories/',        '\Kanso\Admin\Controllers\Dashboard@dispatch', 'categories');
$this->get('/admin/categories/(:all)',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'categories');
$this->post('/admin/categories/',  	    '\Kanso\Admin\Controllers\Dashboard@dispatch', 'categories');
$this->post('/admin/categories/(:all)', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'categories');

# Admin comments
$this->get('/admin/comments/',          '\Kanso\Admin\Controllers\Dashboard@dispatch', 'comments');
$this->get('/admin/comments/(:all)',    '\Kanso\Admin\Controllers\Dashboard@dispatch', 'comments');
$this->post('/admin/comments/',  	    '\Kanso\Admin\Controllers\Dashboard@dispatch', 'comments');
$this->post('/admin/comments/(:all)',   '\Kanso\Admin\Controllers\Dashboard@dispatch', 'comments');

# Admin comment authors
$this->get('/admin/comment-users/',         '\Kanso\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$this->get('/admin/comment-users/(:all)',   '\Kanso\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$this->post('/admin/comment-users/',  	    '\Kanso\Admin\Controllers\Dashboard@dispatch', 'commentUsers');
$this->post('/admin/comments-users/(:all)', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'commentUsers');

# Admin settings
$this->get('/admin/settings/',         '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settings');
$this->post('/admin/settings/',        '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settings');

# Admin account settings
$this->get('/admin/settings/account/',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsAccount');
$this->post('/admin/settings/account/', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsAccount');

# Admin author settings
$this->get('/admin/settings/author/',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsAuthor');
$this->post('/admin/settings/author/', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsAuthor');

# Admin kanso settings
$this->get('/admin/settings/kanso/',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsKanso');
$this->post('/admin/settings/kanso/', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsKanso');

# Admin kanso users
$this->get('/admin/settings/users/',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsUsers');
$this->post('/admin/settings/users/', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsUsers');

# Admin kanso tools
$this->get('/admin/settings/tools/',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsTools');
$this->post('/admin/settings/tools/', '\Kanso\Admin\Controllers\Dashboard@dispatch', 'settingsTools');

# Admin writer
$this->get('/admin/writer/',        '\Kanso\Admin\Controllers\Dashboard@dispatch', 'writer');
$this->get('/admin/writer/(:all)',  '\Kanso\Admin\Controllers\Dashboard@dispatch', 'writer');
$this->post('/admin/writer/',       '\Kanso\Admin\Controllers\Ajax@dispatch', 'writerAjax');
$this->post('/admin/writer/(:any)', '\Kanso\Admin\Controllers\Ajax@dispatch', 'writerAjax');

# Admin media
$this->get('/admin/media/',          '\Kanso\Admin\Controllers\Dashboard@dispatch', 'media');
$this->post('/admin/media-library/', '\Kanso\Admin\Controllers\Ajax@dispatch', 'mediaLibrary');

# Homepage
$this->get('/', '\Kanso\Kanso::loadTemplate', 'home');
$this->get('/page/(:num)/', '\Kanso\Kanso::loadTemplate', 'home');
$this->get('/feed/', '\Kanso\Kanso::loadRssFeed', 'home');
$this->get('/feed/rss/', '\Kanso\Kanso::loadRssFeed', 'home');
$this->get('/feed/atom/', '\Kanso\Kanso::loadRssFeed', 'home');
$this->get('/feed/rdf/', '\Kanso\Kanso::loadRssFeed', 'home');

# Category
if ($this->Config['KANSO_ROUTE_CATEGORIES'] === true) {
	$this->get('/category/(:any)/', '\Kanso\Kanso::loadTemplate', 'category');
	$this->get('/category/(:any)/page/(:num)/', '\Kanso\Kanso::loadTemplate', 'category');
	$this->get('/category/(:any)/feed/', '\Kanso\Kanso::loadRssFeed', 'category');
	$this->get('/category/(:any)/feed/rss/', '\Kanso\Kanso::loadRssFeed', 'category');
	$this->get('/category/(:any)/feed/atom/', '\Kanso\Kanso::loadRssFeed', 'category');
	$this->get('/category/(:any)/feed/rdf/', '\Kanso\Kanso::loadRssFeed', 'category');

}

# Tag
if ($this->Config['KANSO_ROUTE_TAGS'] === true) {
	$this->get('/tag/(:any)/', '\Kanso\Kanso::loadTemplate', 'tag');
	$this->get('/tag/(:any)/page/(:num)/', '\Kanso\Kanso::loadTemplate', 'tag');
	$this->get('/tag/(:any)/feed/', '\Kanso\Kanso::loadRssFeed', 'tag');
	$this->get('/tag/(:any)/feed/rss/', '\Kanso\Kanso::loadRssFeed', 'tag');
	$this->get('/tag/(:any)/feed/atom/', '\Kanso\Kanso::loadRssFeed', 'tag');
	$this->get('/tag/(:any)/feed/rdf/', '\Kanso\Kanso::loadRssFeed', 'tag');

}

# Author
if ($this->Config['KANSO_ROUTE_AUTHORS'] === true) {
	foreach ($this->Config['KANSO_AUTHOR_SLUGS'] as $slug) {
		$this->get("/authors/$slug/", '\Kanso\Kanso::loadTemplate', 'author');
		$this->get("/authors/$slug/page/(:num)/", '\Kanso\Kanso::loadTemplate', 'author');
		$this->get("/authors/$slug/feed/", '\Kanso\Kanso::loadRssFeed', 'author');
		$this->get("/authors/$slug/feed/rss/", '\Kanso\Kanso::loadRssFeed', 'author');
		$this->get("/authors/$slug/feed/atom/", '\Kanso\Kanso::loadRssFeed', 'author');
		$this->get("/authors/$slug/feed/rdf/", '\Kanso\Kanso::loadRssFeed', 'author');
	}
}

# Static pages
foreach ($this->Config['KANSO_STATIC_PAGES'] as $slug) {
	# Published static page
	$this->get("/$slug/", '\Kanso\Kanso::loadTemplate', 'page');
	$this->get("/$slug/feed/", '\Kanso\Kanso::loadRssFeed', 'page');
	$this->get("/$slug/feed/rss", '\Kanso\Kanso::loadRssFeed', 'page');
	$this->get("/$slug/feed/atom", '\Kanso\Kanso::loadRssFeed', 'page');
	$this->get("/$slug/feed/rdf", '\Kanso\Kanso::loadRssFeed', 'page');
}

# Search
$this->get('/search-results/(:all)/', '\Kanso\Kanso::loadTemplate', 'search');
$this->get('/opensearch.xml', '\Kanso\Kanso::loadOpenSearch');


# Archive
$this->get('/archive/',  '\Kanso\Kanso::loadTemplate', 'archive');

# Ajax Post Comments
if ($this->Config['KANSO_COMMENTS_OPEN'] === true) {
	$this->post("/comments", '\Kanso\Comments\CommentManager@dispatch');
}

# Sitemap
$this->get("/".$this->Config['KANSO_SITEMAP'], '\Kanso\Kanso::loadSiteMap');

# Articles
$this->get($this->Config['KANSO_PERMALINKS_ROUTE'], '\Kanso\Kanso::loadTemplate', 'single');
$this->get($this->Config['KANSO_PERMALINKS_ROUTE'].'feed/', '\Kanso\Kanso::loadRssFeed', 'single');
$this->get($this->Config['KANSO_PERMALINKS_ROUTE'].'feed/rss/', '\Kanso\Kanso::loadRssFeed', 'single');
$this->get($this->Config['KANSO_PERMALINKS_ROUTE'].'feed/atom/', '\Kanso\Kanso::loadRssFeed', 'single');
$this->get($this->Config['KANSO_PERMALINKS_ROUTE'].'feed/rdf/', '\Kanso\Kanso::loadRssFeed', 'single');



