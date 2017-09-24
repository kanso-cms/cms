<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\config;

use kanso\framework\file\CascadingFilesystem;
use RuntimeException;

/**
 * Cascading file loader
 *
 * @author Joe J. Howard
 */
class Loader
{
    use CascadingFilesystem;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
	}

	/**
	 * Loads the configuration file.
	 *
	 * @access protected
	 * @param  string      $file        File name
	 * @param  null|string $environment Environment
	 * @return array
	 */
	public function load(string $file, string $environment = null): array
	{
		// Load configuration
		foreach($this->getCascadingFilePaths($file) as $path)
		{			
			if(file_exists($path))
			{
				$config = include($path);

				break;
			}
		}

		// Validate
		if(!isset($config))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] config file does not exist.", [__METHOD__, $file]));
		}

		// Merge environment specific configuration

		if($environment !== null)
		{
			$namespace = strpos($file, '::');

			$namespaced = ($namespace === false) ? $environment . '.' . $file : substr_replace($file, $environment . '.', $namespace + 2, 0);

			foreach($this->getCascadingFilePaths($namespaced) as $path)
			{
				if(file_exists($path))
				{
					$config = array_replace_recursive($config, include($path));

					break;
				}
			}
		}

		return $config;
	}

	/**
	 * Saves the configuration data.
	 *
	 * @access protected
	 * @param  array       $data        Data to save
	 * @param  null|string $environment Environment
	 * @return bool
	 */
	public function save(array $data, string $environment = null): bool
	{		
		foreach ($data as $file => $fileData)
		{
			$path = $this->getFilePath($file, null, $environment);
			
			file_put_contents($path, "<?php\nreturn\n".var_export($fileData, true).";\n?>");
		}

		return true;
	}

	/**
	 * Will soon be used to format var_export into better formatting
	 *
	 * @access protected
	 * @param  array       $data        Data to save
	 * @return string
	 */
	private function var_export(array $data): string
	{		
		
	}
}
