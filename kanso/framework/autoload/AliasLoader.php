<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\autoload;

/**
 * Alias class loader.
 *
 * @author Joe J. Howard
 */
class AliasLoader
{
	/**
	 * Class aliases.
	 *
	 * @var array
	 */
	protected $aliases;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $aliases Class aliases
	 */
	public function __construct(array $aliases)
	{
		$this->aliases = $aliases;
	}

	/**
	 * Autoloads aliased classes.
	 *
	 * @access public
	 * @param  string $alias Class alias
	 * @return bool
	 */
	public function load(string $alias): bool
	{
		if (array_key_exists($alias, $this->aliases))
		{
			return class_alias($this->aliases[$alias], $alias);
		}

		return false;
	}
}
