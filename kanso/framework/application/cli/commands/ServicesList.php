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
class ServicesList extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Lists available container services.';

	/**
	 * {@inheritdoc}
	 */
	public function execute(): void
	{
		$services = $this->container->keys();
		$cols     = ['<green>Service</green>', '<green>Application Access</green>'];
		$rows     = [];

		sort($services);

		foreach ($services as $service)
		{
			$rows[] = [$service, '<yellow>$kanso->'. $service . '</yellow>'];
		}

		$this->write($this->container->count() . ' available services:');

		$this->table($cols, $rows);
	}
}
