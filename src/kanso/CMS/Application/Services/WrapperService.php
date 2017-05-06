<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Wrappers\Managers\CategoryManager;
use Kanso\CMS\Wrappers\Managers\TagManager;
use Kanso\CMS\Wrappers\Managers\UserManager;
use Kanso\CMS\Wrappers\Managers\CommentManager;
use Kanso\CMS\Wrappers\Managers\PostManager;
use Kanso\CMS\Wrappers\Managers\MediaManager;
use Kanso\CMS\Wrappers\Providers\CategoryProvider;
use Kanso\CMS\Wrappers\Providers\TagProvider;
use Kanso\CMS\Wrappers\Providers\UserProvider;
use Kanso\CMS\Wrappers\Providers\CommentProvider;
use Kanso\CMS\Wrappers\Providers\PostProvider;
use Kanso\CMS\Wrappers\Providers\MediaProvider;

/**
 * Database wrapper setup
 *
 * @author Joe J. Howard
 */
class WrapperService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->registerProviders();

		$this->registerManagers();
	}

	/**
	 * Registers the provider managers
	 *
	 * @access private
	 */
	private function registerManagers()
	{
		$this->container->singleton('CategoryManager', function ($container) 
		{
			return new CategoryManager($container->Database->connection()->builder(), $container->CategoryProvider);
		});

		$this->container->singleton('TagManager', function ($container) 
		{
			return new TagManager($container->Database->connection()->builder(), $container->TagProvider);
		});

		$this->container->singleton('PostManager', function ($container) 
		{
			return new PostManager($container->Database->connection()->builder(), $container->PostProvider);
		});

		$this->container->singleton('MediaManager', function ($container) 
		{
			return new MediaManager(
				$container->Database->connection()->builder(),
				$container->MediaProvider,
				$container->Request->environment(),
				$container->Gatekeeper,
				$container->Config->get('cms.uploads.path'),
				$container->Config->get('cms.uploads.accepted_mime'),
				$container->Config->get('cms.uploads.thumbnail_sizes'),
				$container->Config->get('cms.uploads.thumbnail_quality')
			);
		});

		$this->container->singleton('UserManager', function ($container) 
		{
			return new UserManager(
				$container->Database->connection()->builder(),
				$container->UserProvider,
				$container->Crypto,
				$container->Cookie,
				$container->Session,
				$container->Config,
				$container->Request->environment(),
				$container->Email
			);
		});

		$this->container->singleton('CommentManager', function ($container) 
		{
			return new CommentManager(
				$container->Database->connection()->builder(),
				$container->CommentProvider,
				$container->SpamProtector,
				$container->Email,
				$container->Config,
				$container->Request->environment()
			);
		});
	}

	/**
	 * Registers the wrapper providers
	 *
	 * @access private
	 */
	private function registerProviders()
	{
		$this->container->singleton('MediaProvider', function ($container) 
		{
			return new MediaProvider($container->Database->connection()->builder(), $container->Config->get('cms.uploads.thumbnail_sizes'));
		});

		$this->container->singleton('CommentProvider', function ($container) 
		{
			return new CommentProvider($container->Database->connection()->builder());
		});

		$this->container->singleton('UserProvider', function ($container) 
		{
			return new UserProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('TagProvider', function ($container) 
		{
			return new TagProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('CategoryProvider', function ($container) 
		{
			return new CategoryProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('PostProvider', function ($container) 
		{
			return new PostProvider(
				$container->Database->connection()->builder(),
				$container->Config,
				$container->TagProvider,
				$container->CategoryProvider,
				$container->MediaProvider,
				$container->CommentProvider,
				$container->UserProvider
			);
		});
	}
}
