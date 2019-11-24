<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli\output\helpers;

use kanso\framework\cli\output\Output;
use RuntimeException;

/**
 * CLI table.
 *
 * @author Joe J. Howard
 */
class Table
{
	/**
	 * Output instance.
	 *
	 * @var \kanso\framework\cli\output\Output
	 */
	private $output;

	/**
	 * Formatter.
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
	 * Checks if the number of cells in each row matches the number of columns.
	 *
	 * @param  array $columnNames Array of column names
	 * @param  array $rows        Array of rows
	 * @return bool
	 */
	private function isValidInput(array $columnNames, array $rows): bool
	{
		$columns = count($columnNames);

		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				if(count($row) !== $columns)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns the width of the string without formatting.
	 *
	 * @param  string $string String to strip
	 * @return int
	 */
	private function stringWidthWithoutFormatting(string $string): int
	{
		return (int) mb_strwidth($this->formatter !== null ? $this->formatter->stripTags($string) : $string);
	}

	/**
	 * Returns an array containing the maximum width of each column.
	 *
	 * @param  array $columnNames Array of column names
	 * @param  array $rows        Array of rows
	 * @return array
	 */
	private function getColumnWidths(array $columnNames, array $rows): array
	{
		$columnWidths = [];

		// First we'll get the width of the column names

		foreach(array_values($columnNames) as $key => $value)
		{
			$columnWidths[$key] = $this->stringWidthWithoutFormatting($value);
		}

		// Then we'll go through each row and check if the cells are wider than the column names

		foreach($rows as $row)
		{
			foreach(array_values($row) as $key => $value)
			{
				$width = $this->stringWidthWithoutFormatting($value);

				if($width > $columnWidths[$key])
				{
					$columnWidths[$key] = $width;
				}
			}
		}

		// Return array of column widths

		return $columnWidths;
	}

	/**
	 * Builds a row separator.
	 *
	 * @param  array  $columnWidths Array of column widths
	 * @param  string $separator    Separator character
	 * @return string
	 */
	private function buildRowSeparator(array $columnWidths, string $separator = '-'): string
	{
		$columns = count($columnWidths);

		return str_repeat($separator, array_sum($columnWidths) + (($columns * 4) - ($columns - 1))) . PHP_EOL;
	}

	/**
	 * Builds a table row.
	 *
	 * @param  array  $colums       Array of column values
	 * @param  array  $columnWidths Array of column widths
	 * @return string
	 */
	private function buildTableRow(array $colums, array $columnWidths): string
	{
		$cells = [];

		foreach(array_values($colums) as $key => $value)
		{
			$cells[] = $value . str_repeat(' ', $columnWidths[$key] - $this->stringWidthWithoutFormatting($value));
		}

		return '| ' . implode(' | ', $cells) . ' |' . PHP_EOL;
	}

	/**
	 * Renders a table.
	 *
	 * @param  array  $columnNames Array of column names
	 * @param  array  $rows        Array of rows
	 * @return string
	 */
	public function render(array $columnNames, array $rows): string
	{
		if(!$this->isValidInput($columnNames, $rows))
		{
			throw new RuntimeException('The number of cells in each row must match the number of columns.');
		}

		$columnWidths = $this->getColumnWidths($columnNames, $rows);

		// Build table header

		$table = $this->buildRowSeparator($columnWidths)
		. $this->buildTableRow($columnNames, $columnWidths)
		. $this->buildRowSeparator($columnWidths);

		// Add table rows

		foreach($rows as $row)
		{
			$table .= $this->buildTableRow($row, $columnWidths);
		}

		// Add bottom border

		$table .= $this->buildRowSeparator($columnWidths);

		// Return table

		return $table;
	}
}
