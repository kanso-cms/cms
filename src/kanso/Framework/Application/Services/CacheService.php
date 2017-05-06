<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\Framework\Cache\Stores\FileStore;
use Kanso\Framework\Cache\Cache;

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
     * @return mixed
     */
	private function loadCacheStore($cacheConfiguration)
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
     * @return \Kanso\Framework\Cache\Stores\FileStore
     */
	private function fileStore(string $path): FileStore
	{
		return new FileStore($path);	
	}
}
