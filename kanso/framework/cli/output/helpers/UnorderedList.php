<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli\output\helpers;

use kanso\framework\cli\output\Output;

/**
 * CLI unordered list.
 *
 * @author Joe J. Howard
 */
class UnorderedList
{
	/**
	 * Padding.
	 *
	 * @var string
	 */
	private $padding = '  ';

	/**
	 * Output instance.
	 *
	 * @var \kanso\framework\cli\output\Output
	 */
	private $output;

	/**
	 * Constructor.
	 *
	 * @param \kanso\framework\cli\output\Output $output Output instance
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * Builds a list item.
	 *
	 * @param  string $item         Item
	 * @param  string $marker       Item marker
	 * @param  int    $nestingLevel Nesting level
	 * @return string
	 */
	private function buildListItem(string $item, string $marker, int $nestingLevel): string
	{
		return str_repeat($this->padding, $nestingLevel) . "{$marker} {$item}" . PHP_EOL;
	}

	/**
	 * Builds an unordered list.
	 *
	 * @param  array  $items        Items
	 * @param  string $marker       Item marker
	 * @param  int    $nestingLevel Nesting level
	 * @return string
	 */
	private function buildList(array $items, string $marker, int $nestingLevel = 0): string
	{
		$list = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= $this->buildList($item, $marker, $nestingLevel + 1);
			}
			else
			{
				$list .= $this->buildListItem($item, $marker, $nestingLevel);
			}
		}

		return $list;
	}

	/**
	 * Renders an unordered list.
	 *
	 * @param  array  $items  Items
	 * @param  string $marker Item marker
	 * @return string
	 */
	public function render(array $items, string $marker = '*'): string
	{
		return $this->buildList($items, $marker);
	}
}
