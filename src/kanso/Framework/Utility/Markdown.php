<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Utility;

use Kanso\Framework\Utility\Markdown\Parsedown;
use Kanso\Framework\Utility\Markdown\ParsedownExtra;

/**
 * Convert markdown to HTML
 *
 * @author Joe J. Howard
 */
class Markdown
{
	/**
     * Convert markdown to HTML
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
}
