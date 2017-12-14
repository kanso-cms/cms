<?php

use kanso\Kanso;

/**
 * Browser caching for assets
 * 
 * @return string
 */
function admin_assets_version()
{
	return Kanso::VERSION;
}

/**
 * Get the page name
 * 
 * @return string
 */
global $admin_page_request;
$admin_page_request = $ADMIN_PAGE_TYPE;

function admin_page_name()
{	
	global $admin_page_request;

	return Kanso::instance()->Filters->apply('adminRequestName', $admin_page_request);
}

/**
 * Build the Admin page title
 * 
 * @return string
 */
function admin_the_title()
{
	$requestName = admin_page_name();

	# Default title
	$title = 'Kanso';

	# Dashboard pages
	if ($requestName === 'writer')
	{
		$title  = 'Write | New Article';
		$postId = Kanso::instance()->Request->queries('id');
		
		if ($postId)
		{
			$title = 'Write | '.the_title(intval($postId));
		}
	}
	else if ($requestName === 'posts') {
		$title = 'Posts | Kanso';
	}
	else if ($requestName === 'pages') {
		$title = 'Pages | Kanso';
	}
	else if ($requestName === 'tags') {
		$title = 'Tags | Kanso';
	}
	else if ($requestName === 'comments') {
		$title = 'Comments | Kanso';
	}
	else if ($requestName === 'commentUsers') {
		$title = 'Commenters | Kanso';
	}
	else if ($requestName === 'categories') {
		$title = 'Categories | Kanso';
	} 
	else if ($requestName === 'mediaLibrary') {
		$title = 'Media Library | Kanso';
	}
	else if ($requestName === 'settings' || $requestName === 'settingsAccount') {
		$title = 'Account Settings | Kanso';
	}
	else if ($requestName === 'settingsAuthor') {
		$title = 'Author Settings | Kanso';
	}
	else if ($requestName === 'settingsKanso') {
		$title = 'Kanso Settings | Kanso';
	}
	else if ($requestName === 'settingsAccess') {
		$title = 'Access & Security Settings | Kanso';
	}
	else if ($requestName === 'settingsUsers') {
		$title = 'Users | Kanso';
	}
	else if ($requestName === 'settingsTools') {
		$title = 'Tools | Kanso';
	}

	# Account pages
	else if ($requestName === 'login') {
		$title = 'Login | Kanso';
	}
	else if ($requestName === 'forgotpassword') {
		$title = 'Forgot Your Password | Kanso';
	}
	else if ($requestName === 'forgotusername') {
		$title = 'Forgot Your Username | Kanso';
	}
	else if ($requestName === 'register') {
		$title = 'Register | Kanso';
	}
	else if ($requestName === 'resetpassword') {
		$title = 'Reset Your Password | Kanso';
	}

	# Fallback
	else {
		$title = ucfirst($requestName).' | Kanso';
	}

	return Kanso::instance()->Filters->apply('adminPageTitle', $title);
}

/**
 * Build the Admin favicons
 * 
 * @return string
 */
function admin_favicons()
{
	# Default favicons
	$favicons = [
		'<link rel="shortcut icon"                    href="'.admin_assets_url().'/images/favicon.png">',
		'<link rel="apple-touch-icon" sizes="57x57"   href="'.admin_assets_url().'/images/apple-touch-icon.png">',
		'<link rel="apple-touch-icon" sizes="72x72"   href="'.admin_assets_url().'/images/apple-touch-icon-72x72.png">',
		'<link rel="apple-touch-icon" sizes="114x114" href="'.admin_assets_url().'/images/apple-touch-icon-114x114.png">',
	];

	return implode("\n", $favicons);
}

/**
 * Build the Admin style sheets
 * 
 * @return string
 */
function admin_header_scripts()
{
	$stylesheets = [
		'<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700" rel="stylesheet">',
		'<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">',
		'<link rel="stylesheet" href="'.admin_assets_url().'/css/hubble.css?v='.admin_assets_version().'">',
		'<link rel="stylesheet" href="'.admin_assets_url().'/css/theme.css?v='.admin_assets_version().'">',
		'<link rel="stylesheet" href="'.admin_assets_url().'/css/vendor/dropzone.css?v='.admin_assets_version().'">',
	];

	if (admin_page_name() === 'writer')
	{
		$stylesheets[] = '<link rel="stylesheet" href="'.admin_assets_url().'/css/vendor/codemirror.css?v='.admin_assets_version().'">';
        $stylesheets[] = '<link rel="stylesheet" href="'.admin_assets_url().'/css/vendor/highlight.css?v='.admin_assets_version().'">';
        $stylesheets[] = '<link rel="stylesheet" href="'.admin_assets_url().'/css/vendor/offline.css?v='.admin_assets_version().'">';
        $stylesheets[] = '<link rel="stylesheet" href="'.admin_assets_url().'/css/markdown.css?v='.admin_assets_version().'">';
        $stylesheets[] = '<link rel="stylesheet" href="'.admin_assets_url().'/css/writer.css?v='.admin_assets_version().'">';
	}

	$stylesheets = Kanso::instance()->Filters->apply('adminHeaderScripts', $stylesheets);

	return implode("\n", $stylesheets);
}

/**
 * Build the Admin footer scrips
 * 
 * @return string
 */
function admin_footer_scripts()
{
	$scripts = [];

	# Hubble
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/hubble.js?v='.admin_assets_version().'"></script>';

	# Sidebar
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/sidebar.js?v='.admin_assets_version().'"></script>';

	# Lists
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/lists.js?v='.admin_assets_version().'"></script>';

	# forms
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/forms.js?v='.admin_assets_version().'"></script>';

	# Dropzone
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/dropzone.js?v='.admin_assets_version().'"></script>';

	# Media library
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/media-library.js?v='.admin_assets_version().'"></script>';

	# Author avatar
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/author-avatar.js?v='.admin_assets_version().'"></script>';

	# Settings tools
	$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/tools.js?v='.admin_assets_version().'"></script>';

	if (admin_page_name() === 'writer')
	{
		$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/offline.js?v='.admin_assets_version().'"></script>';
		$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/JavaScriptSpellCheck/include.js?v='.admin_assets_version().'"></script>';
		$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/clipboard.js?v='.admin_assets_version().'"></script>';
		$scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/codemirror.js?v='.admin_assets_version().'"></script>';
        $scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/highlight.js?v='.admin_assets_version().'"></script>';
        $scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/vendor/markdownIt.js?v='.admin_assets_version().'"></script>';
        $scripts[] = '<script type="text/javascript" src="'.admin_assets_url().'/js/writer.js?v='.admin_assets_version().'"></script>';
	}
	
	$scripts = Kanso::instance()->Filters->apply('adminFooterScripts', $scripts);

	return implode("\n", $scripts);
}

/**
 * Build the sidebar links
 * 
 * @return array
 */
function admin_sirebar_links()
{
	$links = [
		'visit' => [
			'link'     => '/" target="blank',
			'text'     => 'Visit Site',
			'icon'     => 'globe',
			'children' => [],
		],
		'writer' => [
			'link'     => '/admin/writer/',
			'text'     => 'Writer',
			'icon'     => 'font',
			'children' => [],
		],
		'posts' => [
			'link'     => '/admin/posts/',
			'text'     => 'Posts',
			'icon'     => 'align-left',
			'children' => [],
		],
		'pages' => [
			'link'     => '/admin/pages/',
			'text'     => 'Pages',
			'icon'     => 'file',
			'children' => [],
		],
		'tags' => [
			'link'     => '/admin/tags/',
			'text'     => 'Tags',
			'icon'     => 'tags',
			'children' => [],
		],
		'categories' => [
			'link'     => '/admin/categories/',
			'text'     => 'Categories',
			'icon'     => 'bookmark',
			'children' => [],
		],
		'comments' => [
			'link'     => '/admin/comments/',
			'text'     => 'Comments',
			'icon'     => 'comments',
			'children' => [
				'commentUsers' => [
					'link'     => '/admin/comment-users/',
					'text'     => 'Users',
				],
			],
		],
		'mediaLibrary' => [
			'link'     => '/admin/media/',
			'text'     => 'Media',
			'icon'     => 'camera',
			'children' => [],
		],
		'settings' => [
			'link'     => '/admin/settings/',
			'text'     => 'Settings',
			'icon'     => 'cog',
			'children' => [
				'settingsAccount' => [
					'link'     => '/admin/settings/account/',
					'text'     => 'Account',
				],
				'settingsAuthor' => [
					'link'     => '/admin/settings/author/',
					'text'     => 'Author',
				],
			],
		],
	];

	if (Kanso::instance()->Gatekeeper->getUser()->role === 'administrator')
	{
		$links['settings']['children']['settingsKanso'] = [
			'link'     => '/admin/settings/kanso/',
			'text'     => 'Kanso',
		];
		$links['settings']['children']['settingsAccess'] = [
			'link'     => '/admin/settings/access/',
			'text'     => 'Access & Security',
		];
		$links['settings']['children']['settingsUsers'] = [
			'link'     => '/admin/settings/users/',
			'text'     => 'Users',
		];
		$links['settings']['children']['settingsTools'] = [
			'link'     => '/admin/settings/tools/',
			'text'     => 'Tools',
		];
	}

	$links = Kanso::instance()->Filters->apply('adminSidebar', $links);

	# Logout should always be at the bottom
	$links['logout'] = [
		'link'     => '/admin/logout/',
		'text'     => 'Logout',
		'icon'     => 'sign-out',
		'children' => [],
	];

	return $links;
}

/**
 * Get the available post types
 * 
 * @return string
 */
function admin_post_types()
{
	$types = 
	[
		'Post' => 'post',
		'Page' => 'page',
	];

	return Kanso::instance()->Filters->apply('adminPostTypes', $types);
}

/**
 * Is this a dashboard request ?
 * 
 * @return boolean
 */
function admin_is_dash()
{
	$accountPages = ['login', 'resetpassword', 'register', 'forgotpassword', 'forgotusername'];
	
	return !in_array(admin_page_name(), $accountPages);
}

/**
 * Get the assets URL to the admin panel
 * 
 * @return string
 */
function admin_assets_url()
{
	$env = \kanso\Kanso::instance()->Request->environment();

	return str_replace($env->DOCUMENT_ROOT, $env->HTTP_HOST, KANSO_DIR.'/cms/admin/assets');
}

/**
 * Returns a config value
 * 
 * @return mixed
 */
function admin_kanso_config(string $key)
{
	return \kanso\Kanso::instance()->Config->get($key);
}
