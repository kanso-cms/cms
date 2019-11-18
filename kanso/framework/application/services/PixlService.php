<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\pixl\Image;
use kanso\framework\pixl\processor\GD;
use kanso\framework\pixl\processor\ProcessorInterface;

/**
 * UserAgent Crawler Service.
 *
 * @author Joe J. Howard
 */
class PixlService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Pixl', function($container)
		{
			return new Image($this->getImageProcessor($container->Config->get('pixl')), '');
		});
	}

	/**
	 * Returns the image processor.
	 *
	 * @param  array                                              $config Pixl configuration
	 * @return \kanso\framework\pixl\processor\ProcessorInterface
	 */
	private function getImageProcessor(array $config): ProcessorInterface
	{
		if ($config['processor'] === 'GD')
		{
			return new GD(null, $config['compression']);
		}
	}
}
