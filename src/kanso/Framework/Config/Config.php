<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Config;

use Kanso\Framework\Config\Loader;
use Kanso\Framework\Utility\Arr;

/**
 * Kanso framework configuration manager
 *
 * @author Joe J. Howard
 */
class Config
{
	/**
	 * File loader
	 *
	 * @var \Kanso\Framework\Config\Loader
	 */
	 protected $loader;

	/**
	 * Configuration.
	 *
	 * @var array
	 */
	protected $configuration = [];

	/**
	 * Configuration.
	 *
	 * @var string|null
	 */
	protected $environment;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \Kanso\Framework\Config\Loader $loader Config file reader
	 */
	public function __construct(Loader $loader, string $environment = null)
	{
		$this->loader = $loader;

		$this->environment = $environment;
	}

	/**
	 * Returns the config loader.
	 *
	 * @return \Kanso\Framework\Config\Loader
	 */
	public function getLoader(): Loader
	{
		return $this->loader;
	}

	/**
	 * Returns the currently loaded configuration.
	 *
	 * @access public
	 * @return array
	 */
	public function getLoadedConfiguration(): array
	{
		return $this->configuration;
	}

	/**
	 * Sets the environment.
	 *
	 * @access public
	 * @param string $environment Environment name
	 */
	public function setEnvironment(string $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Parses the language key.
	 *
	 * @access protected
	 * @param  string $key Language key
	 * @return array
	 */
	protected function parseKey(string $key): array
	{
		return (strpos($key, '.') === false) ? [$key, null] : explode('.', $key, 2);
	}

	/**
	 * Loads the configuration file.
	 *
	 * @access protected
	 * @param string $file File name
	 */
	protected function load(string $file)
	{
		$this->configuration[$file] = $this->loader->load($file, $this->environment);
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @access public
	 * @param  string     $key     Config key
	 * @param  null|mixed $default Default value to return if config value doesn't exist
	 * @return null|mixed
	 */
	public function get(string $key, $default = null)
	{
		list($file, $path) = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		return $path === null ? $this->configuration[$file] : Arr::get($this->configuration[$file], $path, $default);
	}

	/**
	 * Get a default setting - bypass the environment
	 *
	 * @access public
	 * @param  string     $key     Config key
	 * @param  null|mixed $default Default value to return if config value doesn't exist
	 * @return null|mixed
	 */
	public function getDefault(string $key)
	{
		list($file, $path) = $this->parseKey($key);

		$data = $this->loader->load($file, 'defaults');

		return $path === null ? $data : Arr::get($data, $path, null);
	}

	/**
	 * Sets a config value.
	 *
	 * @access public
	 * @param string $key   Config key
	 * @param mixed  $value Config value
	 */
	public function set(string $key, $value)
	{
		list($file, $path) = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		Arr::set($this->configuration, $key, $value);
	}

	/**
	 * Removes a value from the configuration.
	 *
	 * @access public
	 * @param  string $key Config key
	 * @return bool
	 */
	public function remove(string $key): bool
	{
		return Arr::delete($this->configuration, $key);
	}

	/**
	 * Save the configuration
	 *
	 * @access public
	 * @return mixed
	 */
	public function save(): bool
	{
		if (!empty($this->configuration) && $this->environment !== 'defaults')
		{
			return $this->loader->save($this->configuration, $this->environment);
		}

		return false;
	}
}
