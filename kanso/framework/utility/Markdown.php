<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

use kanso\framework\utility\markdown\Parsedown;
use kanso\framework\utility\markdown\ParsedownExtra;

/**
 * Convert markdown to HTML.
 *
 * @author Joe J. Howard
 */
class Markdown
{
	/**
	 * Convert markdown to HTML.
	 *
	 * @access public
	 * @see    https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet
	 * @param  string $text  Text in markdown
	 * @param  bool   $extra Convert with markdown extra
	 * @return string
	 */
	public static function convert(string $text, bool $extra = true): string
	{
		$parser = $extra ? new ParsedownExtra : new Parsedown;

		return $parser->text($text);
	}

    /**
     * Converts markdown to plain text.
     *
     * @access public
     * @param  string $str The input string
     * @return string
     */
    public static function plainText(string $str): string
    {
        return trim(preg_replace('/[\r\n]+/', ' ', strip_tags(self::convert(trim($str)))));
    }
}
