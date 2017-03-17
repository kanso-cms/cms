<?php

namespace Kanso\Admin\Models;

/**
 * Admin Includes helper
 *
 * This model is passed to all admin requests as the $ADMIN_INCLUDES 
 * variable. It serves as a helper library for loading parts of the admin panel
 * such as sidebar, scripts, stylesheets etc...
 * 
 * This class is instantiated by Kanso\Admin\Admin
 */
class Includes
{

	/**
     * @var string
     */
    protected $pageName;

    /**
     * Constructor
     *
     */
    public function __construct($pageName)
    {
        $this->pageName = $pageName;
    }

    /**
	 * Get the page name
	 * 
	 * @return string
	 */
	public function pageName()
	{
		return $this->pageName;
	}

    /**
	 * Build the Admin page title
	 * 
	 * @return string
	 */
    public function theTitle()
    {

		# Figure out the title based on the 
		# request type

		# Default title
		$title = 'Kanso';

		# Dashboard pages
		if ($this->pageName === 'writer') {
			$title = 'Write | New Article';
		}
		else if ($this->pageName === 'articles') {
			$title = 'Articles | Kanso';
		}
		else if ($this->pageName === 'pages') {
			$title = 'Pages | Kanso';
		}
		else if ($this->pageName === 'tags') {
			$title = 'Tags | Kanso';
		}
		else if ($this->pageName === 'comments') {
			$title = 'Comments | Kanso';
		}
		else if ($this->pageName === 'commentUsers') {
			$title = 'Commenters | Kanso';
		}
		else if ($this->pageName === 'categories') {
			$title = 'Categories | Kanso';
		} 
		else if ($this->pageName === 'articles') {
			$title = 'Articles | Kanso';
		}
		else if ($this->pageName === 'settings' || $this->pageName === 'settingsAccount') {
			$title = 'Account Settings | Kanso';
		}
		else if ($this->pageName === 'settingsAuthor') {
			$title = 'Author Settings | Kanso';
		}
		else if ($this->pageName === 'settingsKanso') {
			$title = 'Kanso Settings | Kanso';
		}
		else if ($this->pageName === 'settingsUsers') {
			$title = 'Users | Kanso';
		}
		else if ($this->pageName === 'settingsTools') {
			$title = 'Tools | Kanso';
		}

		# Account pages
		else if ($this->pageName === 'login') {
			$title = 'Login | Kanso';
		}
		else if ($this->pageName === 'forgotpassword') {
			$title = 'Forgot Your Password | Kanso';
		}
		else if ($this->pageName === 'forgotusername') {
			$title = 'Forgot Your Username | Kanso';
		}
		else if ($this->pageName === 'register') {
			$title = 'Register | Kanso';
		}
		else if ($this->pageName === 'resetpassword') {
			$title = 'Reset Your Password | Kanso';
		}

		# Fallback
		else {
			$title = ucfirst($this->pageName).' | Kanso';
		}

		# Filter the title
		return \Kanso\Filters::apply('adminPageTitle', $title);
    }

	/**
	 * Build the Admin favicons
	 * 
	 * @return string
	 */
	public function favicons()
	{
		# Default favicons
		$favicons = [
			'<link rel="shortcut icon"                    href="'.$this->assetsURL().'/images/favicon.png">',
			'<link rel="apple-touch-icon" sizes="57x57"   href="'.$this->assetsURL().'/images/apple-touch-icon.png">',
			'<link rel="apple-touch-icon" sizes="72x72"   href="'.$this->assetsURL().'/images/apple-touch-icon-72x72.png">',
			'<link rel="apple-touch-icon" sizes="114x114" href="'.$this->assetsURL().'/images/apple-touch-icon-114x114.png">',
		];
		$favicons = \Kanso\Filters::apply('adminFavicons', $favicons);

		return implode("\n", $favicons);
	}

	/**
	 * Build the Admin style sheets
	 * 
	 * @return string
	 */
	function headerScripts()
	{
		$styles = [
			'<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700" rel="stylesheet">',
			'<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">',
			'<link rel="stylesheet" href="'.$this->assetsURL().'/css/hubble.css?v='.$this->assetsVersion().'">',
			'<link rel="stylesheet" href="'.$this->assetsURL().'/css/theme.css?v='.$this->assetsVersion().'">'
		];
		$styles = \Kanso\Filters::apply('adminHeaderScripts', $styles);

		return implode("\n", $styles);
	}

	/**
	 * Build the Admin footer scrips
	 * 
	 * @return string
	 */
	function footerScripts()
	{
		$scripts = [];

		# Hubble
		$scripts[] = '<script type="text/javascript" src="'.$this->assetsURL().'/js/hubble.js?v='.$this->assetsVersion().'"></script>';

		# Sidebar
		$scripts[] = '<script type="text/javascript" src="'.$this->assetsURL().'/js/sidebar.js?v='.$this->assetsVersion().'"></script>';

		# Lists
		$scripts[] = '<script type="text/javascript" src="'.$this->assetsURL().'/js/lists.js?v='.$this->assetsVersion().'"></script>';

		# forms
		$scripts[] = '<script type="text/javascript" src="'.$this->assetsURL().'/js/forms.js?v='.$this->assetsVersion().'"></script>';

		$scripts = \Kanso\Filters::apply('adminFooterScripts', $scripts);

		return implode("\n", $scripts);
	}

	/**
	 * Build the sidebar links
	 * 
	 * @return array
	 */
	function sidebarLinks()
	{
		$links = [
			'writer' => [
				'link'     => '/admin/writer/',
				'text'     => 'Writer',
				'icon'     => 'font',
				'children' => [],
			],
			'articles' => [
				'link'     => '/admin/articles/',
				'text'     => 'Articles',
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

		if ($this->user('role') === 'administrator') {
			$links['settings']['children']['settingsKanso'] = [
				'link'     => '/admin/settings/kanso/',
				'text'     => 'Kanso',
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

		# Filter the links
		$links =  \Kanso\Filters::apply('adminSidebarLinks', $links);

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
	 * Get the current user
	 * 
	 * @return mixed
	 */
	function user($key = null)
	{
		$user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
		if ($key && $user) {
			if (isset($user->$key)) return $user->$key;
			return null;
		}
		return $user;
	}

	/**
	 * Is this a dashboard request ?
	 * 
	 * @return boolean
	 */
	function isDash()
	{
		$accountPages = ['login', 'resetpassword', 'register', 'forgotpassword', 'forgotusername'];
		return !in_array($this->pageName, $accountPages);
	}

	/**
	 * Get the assets URL to the admin panel
	 * 
	 * @return string
	 */
    public function assetsURL()
	{
		return str_replace('/admin', '/kanso/admin', \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_URI']).'/assets';
	}

	/**
	 * Browser caching for assets
	 * 
	 * @return string
	 */
	public function assetsVersion()
	{
		return \Kanso\Kanso::getInstance()->Version;
	}

	/**
	 * Get Kanso configuration options
	 * 
	 * @return string
	 */
	public function adminKansoConfig($key = null)
	{
		$config = \Kanso\Kanso::getInstance()->Config;
		if ($key) {
			if (array_key_exists($key, $config)) return $config[$key];
			return false;
		}
		return $config;
	}



}