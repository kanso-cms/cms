<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli\output\helpers;

use kanso\framework\cli\output\Output;

/**
 * CLI ordered list.
 *
 * @author Joe J. Howard
 */
class OrderedList
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
	 * Formatter instance.
	 *
	 * @var \kanso\framework\cli\output\Formatter
	 */
	private $formatter;

	/**
	 * Constructor.
	 *
	 * @param \kanso\framework\cli\output\Output $output Output instance
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;

		$this->formatter = $output->formatter();
	}

	/**
	 * Calculates the maximum width of a marker in a list.
	 *
	 * @param  array  $items  Items
	 * @param  string $marker Item marker
	 * @return array
	 */
	private function calculateWidth(array $items, string $marker): array
	{
		$count = 0;

		foreach($items as $item)
		{
			if(!is_array($item))
			{
				$count++;
			}
		}

		$number = strlen(strval($count));

		$marker = strlen(sprintf($this->formatter === null ? $marker : $this->formatter->stripTags($marker), '')) + $number;

		return ['number' => $number, 'marker' => $marker];
	}

	/**
	 * Builds a list item.
	 *
	 * @param  string $item         Item
	 * @param  string $marker       Item marker
	 * @param  int    $width        Item number width
	 * @param  int    $number       Item number
	 * @param  int    $nestingLevel Nesting level
	 * @param  int    $parentWidth  Parent width
	 * @return string
	 */
	private function buildListItem(string $item, string $marker, int $width, int $number, int $nestingLevel, int $parentWidth): string
	{
		$marker = str_repeat(' ', $width - strlen(strval($number))) . sprintf($marker, $number);

		return str_repeat($this->padding, $nestingLevel) . str_repeat(' ', $parentWidth) . "{$marker} {$item}" . PHP_EOL;
	}

	/**
	 * Builds an ordered list.
	 *
	 * @param  array  $items        Items
	 * @param  string $marker       Item marker
	 * @param  int    $nestingLevel Nesting level
	 * @param  int    $parentWidth  Parent marker width
	 * @return string
	 */
	private function buildList(array $items, string $marker, int $nestingLevel = 0, int $parentWidth = 0): string
	{
		$width  = $this->calculateWidth($items, $marker);
		$number = 0;
		$list   = '';

		foreach($items as $item)
		{
			if(is_array($item))
			{
				$list .= $this->buildList($item, $marker, ($nestingLevel + 1), ($width['marker'] - 1 + $parentWidth));
			}
			else
			{
				$list .= $this->buildListItem($item, $marker, $width['number'], ++$number, $nestingLevel, $parentWidth);
			}
		}

		return $list;
	}

	/**
	 * Renders an ordered list.
	 *
	 * @param  array  $items  Items
	 * @param  string $marker Item marker
	 * @return string
	 */
	public function render(array $items, string $marker = '%s.'): string
	{
		return $this->buildList($items, $marker);
	}
}
