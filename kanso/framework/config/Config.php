<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\config;

use kanso\framework\utility\Arr;

/**
 * Kanso framework configuration manager.
 *
 * @author Joe J. Howard
 */
class Config
{
	 /**
	  * File loader.
	  *
	  * @var \kanso\framework\config\Loader
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
	 * @param \kanso\framework\config\Loader $loader Config file reader
	 */
	public function __construct(Loader $loader, string $environment = null)
	{
		$this->loader = $loader;

		$this->environment = $environment;
	}

	/**
	 * Returns the config loader.
	 *
	 * @return \kanso\framework\config\Loader
	 */
	public function getLoader(): Loader
	{
		return $this->loader;
	}

	/**
	 * Returns the currently loaded configuration.
	 *
	 * @return array
	 */
	public function getLoadedConfiguration(): array
	{
		return $this->configuration;
	}

	/**
	 * Sets the environment.
	 *
	 * @param string $environment Environment name
	 */
	public function setEnvironment(string $environment): void
	{
		$this->environment = $environment;
	}

	/**
	 * Parses the language key.
	 *
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
	 * @param string $file File name
	 */
	protected function load(string $file): void
	{
		$this->configuration[$file] = $this->loader->load($file, $this->environment);
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @param  string     $key     Config key
	 * @param  null|mixed $default Default value to return if config value doesn't exist
	 * @return null|mixed
	 */
	public function get(string $key, $default = null)
	{
		[$file, $path] = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		return $path === null ? $this->configuration[$file] : Arr::get($this->configuration[$file], $path, $default);
	}

	/**
	 * Get a default setting - bypass the environment.
	 *
	 * @param  string     $key Config key
	 * @return null|mixed
	 */
	public function getDefault(string $key)
	{
		[$file, $path] = $this->parseKey($key);

		$data = $this->loader->load($file, 'defaults');

		return $path === null ? $data : Arr::get($data, $path, null);
	}

	/**
	 * Sets a config value.
	 *
	 * @param string $key   Config key
	 * @param mixed  $value Config value
	 */
	public function set(string $key, $value): void
	{
		[$file, $path] = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		Arr::set($this->configuration, $key, $value);
	}

	/**
	 * Removes a value from the configuration.
	 *
	 * @param  string $key Config key
	 * @return bool
	 */
	public function remove(string $key): bool
	{
		return Arr::delete($this->configuration, $key);
	}

	/**
	 * Save the configuration.
	 *
	 * @return bool
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
