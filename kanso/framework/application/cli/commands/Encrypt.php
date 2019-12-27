<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\cli\commands;

use kanso\framework\console\Command;

/**
 * Generate application secret.
 *
 * @author Joe J. Howard
 */
class Encrypt extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Secularly encrypts a string';

	/**
	 * {@inheritdoc}
	 */
	protected $commandInformation =
	[
		['--string', 'String to encrypt', 'No'],
	];

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{
		$data = $this->input->parameter('string');

		if (!$data)
		{
			$this->error('Error: No string provided. To encrypt a string please provide a string [php console encrypt --string=foobar ]');

			$this->write('To see a list of available options use the [--help] argument', 'yellow');

			return;
		}

		$this->write('Success: Your encrypted string is:', 'green');

		$this->write($this->container->Crypto->encrypt($data), 'yellow');
	}
}
