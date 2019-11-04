<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\cli\commands;

use kanso\framework\console\Command;
use kanso\framework\security\crypto\Key;
use kanso\framework\application\cli\Application;

/**
 * Generate application secret
 *
 * @author Joe J. Howard
 */
class GenerateSecret extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Generates a new application secret.';

	/**
	 * {@inheritdoc}
	 */
	public function execute()
	{
		$secret = Key::generateEncoded();

		$this->output->writeLn('<green>Success: A new application secret has been generated.</green>');
	}
}