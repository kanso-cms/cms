<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\shortcode;

/**
 * Shortcode handler.
 *
 * @author Joe J. Howard
 */
class Shortcodes
{
	/**
	 * Array of registered shortcode tags.
	 *
	 * @var array
	 */
	private $shortcodes = [];

	/**
	 * Adds a new shortcode.
	 *
	 * Care should be taken through prefixing or other means to ensure that the
	 * shortcode tag being added is unique and will not conflict with other,
	 * already-added shortcode tags. In the event of a duplicated tag, the tag
	 * loaded last will take precedence.
	 *
	 *
	 * @param string $tag      Shortcode tag to be searched in post content.
	 * @param mixed  $callback The callback function to run when the shortcode is found.
	 *                         Every shortcode callback is passed three parameters by default - $content, $attributes, $tag
	 */
	public function register(string $tag, $callback): void
	{
		if ('' == trim($tag))
		{
			return;
		}

		if (0 !== preg_match('@[<>&/\[\]\x00-\x20=]@', $tag))
		{
			return;
		}

		$this->shortcodes[] = new Shortcode($tag, $callback);
	}

	/**
	 * Removes hook for shortcode.
	 *
	 * @param string $tag Shortcode tag to remove hook for.
	 */
	public function remove(string $tag): void
	{
		unset($this->shortcodes[$tag]);
	}

	/**
	 * Clear all shortcodes.
	 */
	public function clear(): void
	{
		$this->shortcodes = [];
	}

	/**
	 * Whether a registered shortcode exists named $tag.
	 *
	 * @param  string $tag Shortcode tag to check.
	 * @return bool
	 */
	public function exists(string $tag): bool
	{
		return array_key_exists($tag, $this->shortcodes);
	}

	/**
	 * Filter text trough shortcodes.
	 *
	 * @param  string $text The text to filter
	 * @return string
	 */
	public function filter(string $text): string
	{
		foreach ($this->shortcodes as $shortcode)
		{
			$text = $shortcode->filter($text);
		}

		return $text;
	}
}
