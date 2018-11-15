<?php

use kanso\Kanso;

/**
 * Browser caching for assets.
 *
 * @return string
 */
function admin_assets_version()
{
	return Kanso::VERSION;
}

/*
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
 * Get the media library.
 *
 * @return string
 */
function admin_media_library(): string
{
	$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'media-library.php';

	$ACCESS_TOKEN = Kanso::instance()->Session->token()->get();

	$contents = Kanso::instance()->View->display($path, ['ACCESS_TOKEN' => $ACCESS_TOKEN]);

	return '<div class="custom-media-lib-wrapper js-triggerable-media">' . $contents . '</div>';
}

/**
 * Build the Admin page title.
 *
 * @return string
 */
function admin_the_title()
{
	$requestName = admin_page_name();

	// Default title
	$title = 'Kanso';

	// Dashboard pages
	if ($requestName === 'writer')
	{
		$title  = 'Write | New Article';
		$postId = Kanso::instance()->Request->queries('id');

		if ($postId)
		{
			$title = 'Write | ' . the_title(intval($postId));
		}
	}
	elseif ($requestName === 'posts') {
		$title = 'Posts | Kanso';
	}
	elseif ($requestName === 'pages') {
		$title = 'Pages | Kanso';
	}
	elseif ($requestName === 'tags') {
		$title = 'Tags | Kanso';
	}
	elseif ($requestName === 'comments') {
		$title = 'Comments | Kanso';
	}
	elseif ($requestName === 'commentUsers') {
		$title = 'Commenters | Kanso';
	}
	elseif ($requestName === 'categories') {
		$title = 'Categories | Kanso';
	}
	elseif ($requestName === 'mediaLibrary') {
		$title = 'Media Library | Kanso';
	}
	elseif ($requestName === 'settings' || $requestName === 'settingsAccount') {
		$title = 'Account Settings | Kanso';
	}
	elseif ($requestName === 'settingsAuthor') {
		$title = 'Author Settings | Kanso';
	}
	elseif ($requestName === 'settingsKanso') {
		$title = 'Kanso Settings | Kanso';
	}
	elseif ($requestName === 'settingsAccess') {
		$title = 'Access & Security Settings | Kanso';
	}
	elseif ($requestName === 'settingsUsers') {
		$title = 'Users | Kanso';
	}
	elseif ($requestName === 'settingsTools') {
		$title = 'Tools | Kanso';
	}
	elseif ($requestName === 'errorLogs') {
		$title = 'Error Logs | Kanso';
	}
	elseif ($requestName === 'emailLogs') {
		$title = 'Email Logs | Kanso';
	}

	// Account pages
	elseif ($requestName === 'login') {
		$title = 'Login | Kanso';
	}
	elseif ($requestName === 'forgotpassword') {
		$title = 'Forgot Your Password | Kanso';
	}
	elseif ($requestName === 'forgotusername') {
		$title = 'Forgot Your Username | Kanso';
	}
	elseif ($requestName === 'register') {
		$title = 'Register | Kanso';
	}
	elseif ($requestName === 'resetpassword') {
		$title = 'Reset Your Password | Kanso';
	}

	// Fallback
	else {
		$title = ucfirst($requestName) . ' | Kanso';
	}

	return Kanso::instance()->Filters->apply('adminPageTitle', $title);
}

/**
 * Build the Admin favicons.
 *
 * @return string
 */
function admin_favicons()
{
	// Default favicons
	$favicons = [
		'<link rel="shortcut icon"                    href="' . admin_assets_url() . '/images/favicon.png">',
		'<link rel="apple-touch-icon" sizes="57x57"   href="' . admin_assets_url() . '/images/apple-touch-icon.png">',
		'<link rel="apple-touch-icon" sizes="72x72"   href="' . admin_assets_url() . '/images/apple-touch-icon-72x72.png">',
		'<link rel="apple-touch-icon" sizes="114x114" href="' . admin_assets_url() . '/images/apple-touch-icon-114x114.png">',
	];

	return implode("\n", $favicons);
}

/**
 * Build the Admin style sheets.
 *
 * @return string
 */
function admin_header_scripts()
{
	$stylesheets = [
		'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">',
		'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900">',
		'<link rel="stylesheet" href="' . admin_assets_url() . '/css/hubble.min.css?v=' . admin_assets_version() . '">',
		'<link rel="stylesheet" href="' . admin_assets_url() . '/css/theme.min.css?v=' . admin_assets_version() . '">',
		'<link rel="stylesheet" href="' . admin_assets_url() . '/css/vendor/dropzone.min.css?v=' . admin_assets_version() . '">',
	];

	if (admin_page_name() === 'writer')
	{
		$stylesheets[] = '<link rel="stylesheet" href="' . admin_assets_url() . '/css/vendor/codemirror.min.css?v=' . admin_assets_version() . '">';
        $stylesheets[] = '<link rel="stylesheet" href="' . admin_assets_url() . '/css/vendor/highlight.min.css?v=' . admin_assets_version() . '">';
        $stylesheets[] = '<link rel="stylesheet" href="' . admin_assets_url() . '/css/vendor/offline.min.css?v=' . admin_assets_version() . '">';
        $stylesheets[] = '<link rel="stylesheet" href="' . admin_assets_url() . '/css/markdown.min.css?v=' . admin_assets_version() . '">';
        $stylesheets[] = '<link rel="stylesheet" href="' . admin_assets_url() . '/css/writer.min.css?v=' . admin_assets_version() . '">';
	}

	$stylesheets = Kanso::instance()->Filters->apply('adminHeaderScripts', $stylesheets);

	return implode("\n", $stylesheets);
}

/**
 * Build the Admin footer scrips.
 *
 * @return string
 */
function admin_footer_scripts()
{
	$scripts = [];

	// Hubble
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/hubble.min.js?v=' . admin_assets_version() . '"></script>';

	// Sidebar
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/sidebar.min.js?v=' . admin_assets_version() . '"></script>';

	// Lists
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/lists.min.js?v=' . admin_assets_version() . '"></script>';

	// forms
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/forms.min.js?v=' . admin_assets_version() . '"></script>';

	// Dropzone
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/dropzone.min.js?v=' . admin_assets_version() . '"></script>';

	// Media library
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/media-library.min.js?v=' . admin_assets_version() . '"></script>';

	// Author avatar
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/author-avatar.min.js?v=' . admin_assets_version() . '"></script>';

	// Settings tools
	$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/tools.min.js?v=' . admin_assets_version() . '"></script>';

	if (admin_page_name() === 'writer')
	{
		$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/offline.min.js?v=' . admin_assets_version() . '"></script>';
		$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/JavaScriptSpellCheck/include.js?v=' . admin_assets_version() . '"></script>';
		$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/clipboard.min.js?v=' . admin_assets_version() . '"></script>';
		$scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/codemirror.min.js?v=' . admin_assets_version() . '"></script>';
        $scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/highlight.min.js?v=' . admin_assets_version() . '"></script>';
        $scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/vendor/markdownIt.min.js?v=' . admin_assets_version() . '"></script>';
        $scripts[] = '<script type="text/javascript" src="' . admin_assets_url() . '/js/writer.min.js?v=' . admin_assets_version() . '"></script>';
	}

	$scripts = Kanso::instance()->Filters->apply('adminFooterScripts', $scripts);

	return implode("\n", $scripts);
}

/**
 * Build the sidebar links.
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
		'content' => [
			'link'     => '/admin/posts/',
			'text'     => 'Content',
			'icon'     => 'align-left',
			'children' =>
			[
				'posts' => [
					'link'     => '/admin/posts/',
					'text'     => 'Posts',
					'icon'     => 'align-left',
				],
				'pages' => [
					'link'     => '/admin/pages/',
					'text'     => 'Pages',
					'icon'     => 'file',
				],
				'tags' => [
					'link'     => '/admin/tags/',
					'text'     => 'Tags',
					'icon'     => 'tags',
				],
				'categories' => [
					'link'     => '/admin/categories/',
					'text'     => 'Categories',
					'icon'     => 'bookmark',
					'children' => [],
				],
				'mediaLibrary' => [
					'link'     => '/admin/media/',
					'text'     => 'Media',
					'icon'     => 'camera',
					'children' => [],
				],
			],
		],
		'crm' => [
			'link'     => '/admin/leads/',
			'text'     => 'CRM',
			'icon'     => 'users',
			'children' => [
				'leads' => [
					'link'     => '/admin/leads/',
					'text'     => 'Leads',
					'icon'     => 'users',
				],
				'comments' => [
					'link'     => '/admin/comments/',
					'text'     => 'Comments',
					'icon'     => 'users',
				],
				'commentUsers' => [
					'link'     => '/admin/comment-users/',
					'text'     => 'Commenters',
					'icon'     => 'users',
				],
			],
		],

		'settings' => [
			'link'     => '/admin/settings/',
			'text'     => 'Settings',
			'icon'     => 'cog',
			'children' => [
				'settingsAccount' => [
					'link'     => '/admin/settings/account/',
					'text'     => 'Account',
					'icon'     => 'user-circle-o',
				],
				'settingsAuthor' => [
					'link'     => '/admin/settings/author/',
					'text'     => 'Author',
					'icon'     => 'address-card',
				],
			],
		],
	];

	if (Kanso::instance()->Gatekeeper->getUser()->role === 'administrator')
	{
		$links['settings']['children']['settingsKanso'] = [
			'link'     => '/admin/settings/kanso/',
			'text'     => 'Kanso',
			'icon'     => 'columns',
		];
		$links['settings']['children']['settingsAccess'] = [
			'link'     => '/admin/settings/access/',
			'text'     => 'Access & Security',
			'icon'     => 'shield',
		];
		$links['settings']['children']['settingsUsers'] = [
			'link'     => '/admin/settings/users/',
			'text'     => 'Users',
			'icon'     => 'users',
		];
		$links['settings']['children']['settingsTools'] = [
			'link'     => '/admin/settings/tools/',
			'text'     => 'Tools',
			'icon'     => 'wrench',
		];
		$links['logs'] = [
			'link'     => '/admin/logs/error-logs/',
			'text'     => 'Logs',
			'icon'     => 'terminal',
			'children' =>
			[
				'errorLogs' => [
					'link'     => '/admin/logs/error-logs/',
					'text'     => 'Error Logs',
					'icon'     => 'bug',
				],
				'emailLogs' => [
					'link'     => '/admin/logs/email-logs/',
					'text'     => 'Email Logs',
					'icon'     => 'envelope',
				],

			],
		];
	}

	$links = Kanso::instance()->Filters->apply('adminSidebar', $links);

	// Logout should always be at the bottom
	$links['logout'] = [
		'link'     => '/admin/logout/',
		'text'     => 'Logout',
		'icon'     => 'sign-out',
		'children' => [],
	];

	return $links;
}

/**
 * Get the available post types.
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
 * @return bool
 */
function admin_is_dash()
{
	$accountPages = ['login', 'resetpassword', 'register', 'forgotpassword', 'forgotusername'];

	return !in_array(admin_page_name(), $accountPages);
}

/**
 * Get the assets URL to the admin panel.
 *
 * @return string
 */
function admin_assets_url()
{
	$env = Kanso::instance()->Request->environment();

	return str_replace($env->DOCUMENT_ROOT, $env->HTTP_HOST, KANSO_DIR . '/cms/admin/assets');
}

/**
 * Returns a config value.
 *
 * @return mixed
 */
function admin_writer_categories(int $postId): string
{
	$categories = Kanso::instance()->Query->the_post($postId)->categories;
	$parents    = [];
	$children   = [];

	foreach ($categories as $category)
	{
		$parent = $category->parent();

		if ($parent)
		{
		    $parents[] = $category->name;

		    while ($parent)
		    {
		        $children[] = $parent->name;
		        $parent     = $parent->parent();
		    }
		}
		else
		{
			$parents[] = $category->name;
		}
	}

	return implode(', ', array_unique(array_merge($parents, $children)));
}
