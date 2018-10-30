<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\wrappers\managers\CategoryManager;
use kanso\cms\wrappers\managers\CommentManager;
use kanso\cms\wrappers\managers\LeadManager;
use kanso\cms\wrappers\managers\MediaManager;
use kanso\cms\wrappers\managers\PostManager;
use kanso\cms\wrappers\managers\TagManager;
use kanso\cms\wrappers\managers\UserManager;
use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\cms\wrappers\providers\CommentProvider;
use kanso\cms\wrappers\providers\LeadProvider;
use kanso\cms\wrappers\providers\MediaProvider;
use kanso\cms\wrappers\providers\PostProvider;
use kanso\cms\wrappers\providers\TagProvider;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\framework\application\services\Service;

/**
 * Database wrapper setup.
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
	 * Registers the provider managers.
	 *
	 * @access private
	 */
	private function registerManagers()
	{
		$this->container->singleton('CategoryManager', function($container)
		{
			return new CategoryManager($container->Database->connection()->builder(), $container->CategoryProvider);
		});

		$this->container->singleton('TagManager', function($container)
		{
			return new TagManager($container->Database->connection()->builder(), $container->TagProvider);
		});

		$this->container->singleton('PostManager', function($container)
		{
			return new PostManager($container->Database->connection()->builder(), $container->PostProvider);
		});

		$this->container->singleton('MediaManager', function($container)
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

		$this->container->singleton('UserManager', function($container)
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

		$this->container->singleton('CommentManager', function($container)
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

		$this->container->singleton('LeadManager', function($container)
		{
			return new LeadManager(
				$container->Database->connection()->builder(),
				$container->LeadProvider,
				$container->Crypto,
				$container->Cookie,
				$container->Session,
				$container->Config,
				$container->Request->environment(),
				$container->Email
			);
		});
	}

	/**
	 * Registers the wrapper providers.
	 *
	 * @access private
	 */
	private function registerProviders()
	{
		$this->container->singleton('MediaProvider', function($container)
		{
			return new MediaProvider($container->Database->connection()->builder(), $container->Config->get('cms.uploads.thumbnail_sizes'));
		});

		$this->container->singleton('CommentProvider', function($container)
		{
			return new CommentProvider($container->Database->connection()->builder());
		});

		$this->container->singleton('UserProvider', function($container)
		{
			return new UserProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('LeadProvider', function($container)
		{
			return new LeadProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('TagProvider', function($container)
		{
			return new TagProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('CategoryProvider', function($container)
		{
			return new CategoryProvider($this->container->Database->connection()->builder());
		});

		$this->container->singleton('PostProvider', function($container)
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
