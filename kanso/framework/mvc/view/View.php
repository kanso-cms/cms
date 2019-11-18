<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\view;

use kanso\Kanso;

/**
 * Default view implementation.
 *
 * @author Joe J. Howard
 */
class View extends ViewBase implements ViewInterface
{
    /**
     * Should the "$kanso" variable be made available to all templates ?
     *
     * @var bool
     */
    private $includeKanso = true;

    /**
     * Should the "$kanso" variable be made available to all templates ?
     *
     * @param bool $toggle Enable/disable local kanso instance (optional) (default true)
     */
    public function includeKanso(bool $toggle = true): void
    {
        $this->includeKanso = $toggle;
    }

	/**
	 * {@inheritdoc}
	 */
	public function include(string $file): void
	{
		$this->includes[$file] = $file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function includes(array $files): void
	{
		foreach ($files as $path)
		{
			$this->include($path);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(string $file, array $data = []): string
	{
		return $this->sandbox($file, $data);
	}

    /**
     * Sandbox and output a template.
     *
     * @param  string $file Absolute path to template file
     * @param  array  $data Assoc array of variables (optional) (default [])
     * @return string
     */
    private function sandbox(string $file, array $data): string
    {
        $data = array_merge($this->data, $data);

        if ($this->includeKanso === true)
        {
            $kanso = Kanso::instance();
        }

        foreach ($this->includes as $include)
        {
            if (file_exists($include))
            {
                require_once $include;
            }
        }

        extract($data);

        ob_start();

        require $file;

        return ob_get_clean();
    }
}
