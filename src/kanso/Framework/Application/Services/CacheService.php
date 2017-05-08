<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\application\services\Service;
use kanso\framework\cache\stores\FileStore;
use kanso\framework\cache\Cache;

/**
 * Cache service
 *
 * @author Joe J. Howard
 */
class CacheService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Cache', function ()
		{
			$cacheConfiguration = $this->container->Config->get('cache.configurations.'.$this->container->Config->get('cache.default'));

			return new Cache($cacheConfiguration['expire'], $this->loadCacheStore($cacheConfiguration));
		});
	}

	/**
     * Get the cache store
     *
     * @access private
     * @param  array   $cacheConfiguration Configuration options for the cache
     * @return mixed
     */
	private function loadCacheStore(array $cacheConfiguration)
	{
		$type = $cacheConfiguration['type'];

		if ($type === 'file')
		{
			return $this->fileStore($cacheConfiguration['path']);
		}
	}

	/**
     * Returns the file storage implementation
     *
     * @access private
     * @param  string  $path Directory to store cached files
     * @return \kanso\framework\cache\stores\FileStore
     */
	private function fileStore(string $path): FileStore
	{
		return new FileStore($path);	
	}
}
