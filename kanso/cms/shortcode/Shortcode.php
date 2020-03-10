<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\shortcode;

use kanso\framework\utility\Callback;

/**
 * Shortcode wrapper.
 *
 * @author Joe J. Howard
 */
class Shortcode
{
    /**
     * @var string
     */
    protected $tag;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @param string $tag      Shortcode tag
     * @param mixed  $callback Callback function for shortcode
     */
    public function __construct(string $tag, $callback)
    {
        $this->tag = $tag;

        $this->callback = $callback;
    }

	/**
	 * Search content for shortcodes and filter shortcode through the callback.
	 *
	 * @param  string $content Content to search for shortcodes.
	 * @return string
	 */
	public function filter(string $content): string
    {
        if (false === strpos($content, '['))
        {
            return $content;
        }

        return $this->parseContent($content);
    }

    /**
     * Match all shortcodes and parse them.
     *
     * @param  string $content The content to be filtered
     * @return string
     */
    private function parseContent(string $content): string
    {
        $pattern = "(\[\s*{$this->tag}[^\]\/]*)(\/\]|(?!\/\])\][^\[]*\[\/{$this->tag}\])";

        return preg_replace_callback("/{$pattern}/", [&$this, 'parseShortcodeMatch'], $content);
    }

    /**
     * Parse a matched shortcode.
     *
     * @param  array  $shortcode The matches from preg_replace
     * @return string
     */
    private function parseShortcodeMatch(array $shortcode): string
    {
        if (!isset($shortcode[0]) || $shortcode[0] === null || trim($shortcode[0]) === '')
        {
            // Invalid
            return '';
        }

        $shortcode = trim($shortcode[0]);
        $content   = $this->parseShortcodeContent($shortcode);
        $attrbutes = $this->parseShortcodeAttributes($shortcode);

        return Callback::apply($this->callback, [$content, $attrbutes, $this->tag]);
    }

    /**
     * Parse and return the shortcode content between the bracket tags.
     *
     * @param  string $shortcode An individual shortcode statement
     * @return string
     */
    private function parseShortcodeContent(string $shortcode): string
    {
        $content       = '';
        $isSelfClosing = (strpos($shortcode, '/]') !== false);
        $pattern       = "\[\s*{$this->tag}[^\]]*\]([^[]*)\[\/{$this->tag}\]";

        if (!$isSelfClosing)
        {
            preg_match("/{$pattern}/", $shortcode, $contentMatch);

            if (isset($contentMatch[1]))
            {
                $content = trim($contentMatch[1]);
            }
        }

        return $content;
    }

    /**
     * Parse and return the shortcode attributes.
     *
     * @param  string $shortcode An individual shortcode statement
     * @return array
     */
    private function parseShortcodeAttributes(string $shortcode): array
    {
        $attrbutes = [];
        $pattern   = "([\w-]+)=\"([^\"]*)\"";

        // Parse attrbutes
        preg_match_all("/{$pattern}/", $shortcode, $attributeMatches);

        $props = array_values(array_filter($attributeMatches[1]));
        $vals  = array_values(array_filter($attributeMatches[2]));

        if (!empty($props) && count($props) === count($vals));
        {
            $attrbutes = array_combine($props, $vals);
        }

        return $attrbutes;
    }
}
