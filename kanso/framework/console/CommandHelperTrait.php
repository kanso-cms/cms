<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\console;

use kanso\framework\cli\output\helpers\Table;
use kanso\framework\cli\output\helpers\OrderedList;
use kanso\framework\cli\output\helpers\UnorderedList;

/**
 * Command helper trait.
 *
 * @author Joe J. Howard
 */
trait CommandHelperTrait
{
	/**
	 * Writes string to output.
	 *
	 * @param string $string String to write
	 * @param string $color  $color tags to wrap text
	 */
	protected function write(string $string, string $color = ''): void
	{
		$output = $color === '' ? $string : "<{$color}>{$string}</{$color}>";

		$this->output->writeLn($output);
	}

	/**
	 * Dumps data to output
	 *
	 * @param mixed $data Data to dump
	 */
	protected function dump($data): void
	{
		$this->output->dump($data);
	}

	/**
	 * Writes string to output using the error writer.
	 *
	 * @param string $string String to write
	 */
	protected function error(string $string): void
	{
		$this->output->writeLn("<red>{$string}</red>");
	}

	/**
	 * Clears the screen.
	 */
	protected function clear(): void
	{
		$this->output->clear();
	}

	/**
	 * Draws a table.
	 *
	 * @param array $columnNames Array of column names
	 * @param array $rows        Array of rows
	 */
	protected function table(array $columnNames, array $rows): void
	{
		$table = new Table($this->output);

		$this->output->write($table->render($columnNames, $rows));
	}

	/**
	 * Draws an ordered list.
	 *
	 * @param array  $items  Items
	 * @param string $marker Item marker
	 */
	protected function ol(array $items, string $marker = '<yellow>%s</yellow>'): void
	{
		$ol = new OrderedList($this->output);

		$this->output->write($ol->render($items, $marker));
	}

	/**
	 * Draws an unordered list.
	 *
	 * @param array  $items  Items
	 * @param string $marker Item marker
	 */
	protected function ul(array $items, string $marker = '<yellow>*</yellow>'): void
	{
		$ul = new UnorderedList($this->output);

		$this->output->write($ul->render($items, $marker));
	}
}
